<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applicationtype extends Model
{
    use HasFactory;

    protected $guarded = [];

    // If your table isn't "applicationtypes", uncomment:
    // protected $table = 'applicationtypes';

    public function applicant()
    {
        return $this->hasMany(Applicant::class); // App\Models\Applicant
    }
}
