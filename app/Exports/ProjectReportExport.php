<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use App\Models\Project;

class ProjectReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
    }

    public function collection()
    {
        return Project::with(['teamLeadInfo', 'assignedBy', 'task.subtask', 'task.assignedTo', 'task.assignedBy'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Project ID',
            'Project Title',
            'Project Code',
            'Team Leader',
            'Assigned By',
            'Total Tasks',
            'Total Subtasks',
            'Task Completion Percentage',
            'Subtask Completion Percentage',
            'Average Completion Time',
            'Overdue Tasks',
        ];
    }

    public function map($row): array
    {
        $tasks = $row->task;

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
            if ($task->status === 'in-progress') {
                return Carbon::parse($task->created_at)->diffInSeconds($task->updated_at);
            } elseif ($task->completed_at) {
                return Carbon::parse($task->created_at)->diffInSeconds($task->completed_at);
            }
            return 0;
        });

        $avgCompletionTime = gmdate('H:i:s', $avgCompletionTimeInSeconds);

        $overdueTasks = $tasks->filter(function ($task) {
            return $task->status !== 'completed' && $task->due_date && Carbon::now()->gt($task->due_date);
        })->count();

        return [
            $row->id,
            $row->title,
            $row->project_code,
            $row->teamLeadInfo->name ?? null,
            $row->assignedBy->name ?? null,
            $totalTasks,
            $totalSubtasks,
            $taskCompletionPercentage,
            $subtaskCompletionPercentage,
            $avgCompletionTime,
            $overdueTasks,
        ];
    }
}

