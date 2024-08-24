<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BasController;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\ProjectReportExport;
use Maatwebsite\Excel\Facades\Excel;


class ReportController extends BasController
{

    public function generateProjectReport(Request $request):JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $projects = Project::with(['teamLeadInfo', 'assignedBy', 'task.subtask', 'task.assignedTo', 'task.assignedBy'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $report = $projects->map(function ($project) {
            $tasks = $project->task;

            $totalTasks = $tasks->count();
            $totalSubtasks = $tasks->sum(function ($task) {
                return $task->subtask->count();
            });

            $completedTasks = $tasks->where('status', 'completed')->count();
            $completedSubtasks = $tasks->sum(function ($task) {
                return $task->subtask->where('status', 'completed')->count();
            });

            $taskCompletionPercentage = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
            $subtaskCompletionPercentage = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;

            $avgCompletionTimeInSeconds = $tasks->avg(function ($task) {
                if ($task->completed_at) {
                      return Carbon::parse($task->updated_at)->diffInSeconds($task->completed_at);
                }
                return 0;
            });


            $avgCompletionTime = gmdate('H:i:s', $avgCompletionTimeInSeconds);

            $overdueTasks = $tasks->filter(function ($task) {
                return $task->status !== 'completed' && $task->due_date && Carbon::now()->gt($task->due_date);
            })->count();


            return [
                'project_id' => $project->id,
                'project_title' => $project->title,
                'project_code' => $project->project_code,
                'team_leader' => $project->teamLeadInfo->name ?? null,
                'assigned_by' => $project->assignedBy->name ?? null,
                'total_tasks' => $totalTasks,
                'total_subtasks' => $totalSubtasks,
                'task_completion_percentage' => $taskCompletionPercentage,
                'subtask_completion_percentage' => $subtaskCompletionPercentage,
                'average_completion_time' => $avgCompletionTime,
                'overdue_tasks' => $overdueTasks,
                'tasks' => $tasks->map(function ($task) {
                    return [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'task_status' => $task->status,
                        'assigned_to' => $task->assignedTo->name ?? null,
                        'assigned_by' => $task->assignedBy->name ?? null,
                        'completed_at' => $task->completed_at,
                        'subtasks' => $task->subtask->map(function ($subtask) {
                            return [
                                'subtask_id' => $subtask->id,
                                'subtask_title' => $subtask->title,
                                'subtask_status' => $subtask->status,
                                'assigned_to' => $subtask->assignedTo->name ?? null,
                                'assigned_by' => $subtask->assignedBy->name ?? null,
                                'completed_at' => $subtask->completed_at,
                            ];
                        }),
                    ];
                }),
            ];
        });
        return $this->sendResponse($report, 'Project report generated successfully.');
    }


    public function exportProjectReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        return Excel::download(new ProjectReportExport($startDate, $endDate), 'project_report.xlsx');
    }
}
