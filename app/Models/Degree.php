<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Degree extends Model
{
    use HasFactory;

    protected $guarded = [];

    // keep method name from your original codebase
    public function applicant()
    {
        return $this->hasMany(Applicant::class);
    }
}
