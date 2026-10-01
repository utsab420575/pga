<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function degrees()
    {
        return $this->belongsToMany(Degree::class, 'department_degree');
    }

    // keep method name from your original codebase
    public function applicant()
    {
        return $this->hasMany(Applicant::class);
    }
    public function user(){
        return $this->hasOne(User::class);
    }
}