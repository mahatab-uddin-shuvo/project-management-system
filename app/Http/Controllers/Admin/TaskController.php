<?php

namespace App\Http\Controllers\Admin;

use App\Events\RealtimeEvent;
use App\Http\Controllers\BasController;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends BasController
{
    public function list(Request $request): JsonResponse
    {
        $limit = $request->get('limit') ?  $request->get('limit') : 10;
        $query = Task::with('projectId','assignedTo','assignedBy','parentTask')->paginate($limit);

        return $this->sendResponse($query, 'Task get successfully.');
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'nullable',
            'status' => 'required|in:pending,in-progress,completed',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'required|exists:users,id',
//            'assigned_by' => 'required|exists:users,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'due_date' => 'required|date_format:Y-m-d',
            'completed_at' => 'nullable|date_format:Y-m-d h:i:s',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $task = new Task();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->status = $request->status;
        $task->project_id = $request->project_id;
        $task->assigned_to = $request->assigned_to;
        $task->assigned_by = Auth::id();
        $task->parent_id = $request->parent_id;
        $task->due_date = $request->due_date;
        $task->completed_at = $request->completed_at;
        $task->save();

        $username = User::where("id",$request->assigned_to)->pluck("name")->first();
        $usernameTeamLead = User::where("id",$request->assigned_by)->pluck("name")->first();
        $message = "This $task->title is assign by $usernameTeamLead assign to $username";
        event(new RealtimeEvent("TeamMember",$task->assigned_to,$message));

        return $this->sendResponse($task, 'Task created successfully.');

    }

    public function update(Request $request,$id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'nullable',
            'status' => 'required|in:pending,in-progress,completed',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'required|exists:users,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'due_date' => 'required|date_format:Y-m-d',
            'completed_at' => 'nullable|date_format:Y-m-d h:i:s',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $task = Task::find($id);
        $task->title = $request->title;
        $task->description = $request->description;
        $task->status = $request->status;
        $task->project_id = $request->project_id;
        $task->assigned_to = $request->assigned_to;
        $task->assigned_by = Auth::id();
        $task->parent_id = $request->parent_id;
        $task->due_date = $request->due_date;
        $task->completed_at = $request->completed_at;
        $task->save();

        if($task->assigned_to != $request->assigned_to){
            $username = User::where("id",$request->assigned_to)->pluck("name")->first();
            $usernameTeamLead = User::where("id",$request->assigned_by)->pluck("name")->first();
            $message = "This $task->title is assign by $usernameTeamLead assign to $username";
            event(new RealtimeEvent("TeamMember",$task->assigned_to,$message));
        }

        return $this->sendResponse($task, 'Task created successfully.');

    }

    public function get($id): JsonResponse
    {
        $query = Task::with('projectId','assignedTo','assignedBy','parentTask')
            ->findorfail($id);

        return $this->sendResponse($query, 'Task get successfully.');
    }

    public function delete($id): JsonResponse
    {
        $query = Task::findorfail($id);
        $query->delete();
        return $this->sendResponse('', 'Tasks deleted successfully.');
    }

    public function subTaskList($taskId): JsonResponse
    {
        $query = Task::with('projectId','assignedTo','assignedBy','parentTask')
            ->where("parent_id",$taskId)
            ->get();

        return $this->sendResponse($query, 'Sub Task get successfully.');
    }


    public function projectWiseTaskList($projectId): JsonResponse
    {
        $query = Project::with('task.subtask','task.assignedTo','teamLeadInfo','assignedBy')
            ->where("id",$projectId)
            ->get();

        return $this->sendResponse($query, 'Sub Task get successfully.');
    }

    public function taskStatusUpdate(Request $request, $subtaskId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in-progress,completed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $task = Task::findOrFail($subtaskId);
        $prevtaskstatus = $task->status;
        $task->status = $request->status;
        $task->completed_at = $request->status === "completed" ? Carbon::now()->format('Y-m-d h:i:s') : null;
        $task->save();

        if($prevtaskstatus != $task->status){
            $username = User::where("id",$task->assigned_to)->pluck("name")->first();
            $message = "This $task->title task is status $task->status changed by $username";
            event(new RealtimeEvent("TeamLeader",$task->assigned_by,$message));
        }

        if ($task->parent_id) {
            $parentTask = Task::find($task->parent_id);

            // Get all sibling subtasks of the parent task
            $subtasks = Task::where('parent_id', $task->parent_id)->get();

            // Check if all subtasks are completed
            $allCompleted = $subtasks->every(function ($subtask) {
                return $subtask->status === 'completed';
            });

            // Check if any subtask is in-progress
            $anyInProgress = $subtasks->contains(function ($subtask) {
                return $subtask->status === 'in-progress';
            });

            // Check if any subtask is pending
            $anyPending = $subtasks->contains(function ($subtask) {
                return $subtask->status === 'pending';
            });

            if ($allCompleted) {
                // If all subtasks are completed, set the main task as completed
                $parentTask->status = 'completed';
                $parentTask->completed_at = Carbon::now()->format('Y-m-d h:i:s');
            } elseif ($anyInProgress && !$anyPending) {
                // If all subtasks are in-progress or completed, set the main task as in-progress
                $parentTask->status = 'in-progress';
                $parentTask->completed_at = null;
            } else {
                // If any subtask is pending, set the main task as pending
                $parentTask->status = 'pending';
                $parentTask->completed_at = null;
            }

            $parentTask->save();
        }
        $task = Task::with('subtask')->where("id",$task->parent_id)->first();

        return $this->sendResponse($task, 'Subtask updated successfully.');
    }


    public function getUserTasks(Request $request,$userId): JsonResponse
    {
        $limit = $request->get('limit') ?  $request->get('limit') : 10;

        $tasks = Task::with('projectId')
            ->where('assigned_to', $userId)
            ->paginate($limit);

        if ($tasks->isEmpty()) {
            return response()->json(['message' => 'No tasks found for this user.'], 404);
        }

        return $this->sendResponse($tasks, 'Subtask updated successfully.');
    }

}
