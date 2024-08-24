<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->project_code = self::convertToAcronym($model->title);
        });

        static::created(function ($model) {
            $model->project_code = $model->project_code . '-' . $model->id;
            $model->save();
        });
    }

    // The conversion method...
    public static function convertToAcronym($input)
    {
        $words = explode(' ', $input);
        $acronym = '';
        foreach ($words as $word) {
            $acronym .= strtoupper($word[0]);
        }
        return $acronym;
    }

    public function teamLeadInfo(){
        return $this->belongsTo(User::class,'team_leader_id','id');
    }
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by','id');
    }

    public function task()
    {
        return $this->hasMany(Task::class, 'project_id','id');
    }


}
