<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Studenttype extends Model
{
    use HasFactory;

    protected $guarded = [];

    // If your table isn't "studenttypes", set it:
    // protected $table = 'studenttypes';

    public function applicant()
    {
        return $this->hasMany(Applicant::class); // App\Models\Applicant
    }
}
