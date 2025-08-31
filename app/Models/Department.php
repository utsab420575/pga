<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $guarded = [];

    // If your table isn't "departments", set it:
    // protected $table = 'departments';

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);     // App\Models\Faculty
    }

    public function applicant()
    {
        return $this->hasMany(Applicant::class);     // App\Models\Applicant
    }
}
