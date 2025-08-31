<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faculty extends Model
{
    use HasFactory;

    protected $guarded = [];

    // If your table isn't "faculties", set it:
    // protected $table = 'faculties';

    // Keep method name as in old code to avoid changing callers
    public function department()
    {
        return $this->hasMany(Department::class); // App\Models\Department
    }
}
