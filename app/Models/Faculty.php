<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faculty extends Model
{
    use HasFactory;

    protected $guarded = [];

    // keep method name from your original codebase
    public function department()
    {
        return $this->hasMany(Department::class);
    }
}
