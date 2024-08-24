<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    public function projectId(){
        return $this->belongsTo(Project::class,'project_id','id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to','id')->select('id','name','email');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by','id')->select('id','name','email');
    }

    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_id','id');
    }

    public function subtask()
    {
        return $this->hasMany(Task::class, 'parent_id','id');
    }
}
