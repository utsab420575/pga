<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Degree extends Model
{
    use HasFactory;

    protected $guarded = [];

    // If your table isn't "degrees", you can set it:
    // protected $table = 'degrees';

    public function applicant()
    {
        return $this->hasMany(Applicant::class); // App\Models\Applicant
    }
}
