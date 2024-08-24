<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BasController;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends BasController
{

    public function list(Request $request): JsonResponse
    {
        $limit = $request->get('limit') ?  $request->get('limit') : 10;
        $query = Project::with('teamLeadInfo','assignedBy')->paginate($limit);

        return $this->sendResponse($query, 'Projects get successfully.');
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:projects,title',
            'description' => 'nullable',
            'team_leader_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $project = new Project();
        $project->title = $request->title;
        $project->description = $request->description;
        $project->team_leader_id = $request->team_leader_id;
        $project->assigned_by = Auth::id();
        $project->save();

        return $this->sendResponse($project, 'Projects created successfully.');

    }

    public function update(Request $request,$id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => "required|unique:projects,title,$id",
            'description' => 'nullable',
            'team_leader_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $project = Project::find($id);
        $project->title = $request->title;
        $project->description = $request->description;
        $project->team_leader_id = $request->team_leader_id;
        $project->assigned_by = Auth::id();
        // Manually update project_code
        $acronym = Project::convertToAcronym($project->title);
        $project->project_code = $acronym . '-' . $project->id;
        $project->save();

        return $this->sendResponse($project, 'Projects created successfully.');

    }

    public function get($id): JsonResponse
    {
        $query = Project::with('teamLeadInfo','assignedBy')
            ->findorfail($id);

        return $this->sendResponse($query, 'Projects get successfully.');
    }

    public function delete($id): JsonResponse
    {
        $query = Project::findorfail($id);
        $query->delete();
        return $this->sendResponse('', 'Projects deleted successfully.');
    }
}
