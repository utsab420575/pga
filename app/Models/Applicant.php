<?php

namespace App\Models;

use App\Models\Applicationtype;
use App\Models\Degree;
use App\Models\Department;
use App\Models\Studenttype;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applicant extends Model
{
    use HasFactory;

    // allow mass assignment for all attributes (validate inputs in controllers/requests)
    protected $guarded = [];

    // If your table name isn't "applicants", uncomment:
    // protected $table = 'applicants';

    // Relationships (assumes these models also live in App\Models)
    public function department()      { return $this->belongsTo(Department::class); }
    public function studenttype()     { return $this->belongsTo(Studenttype::class); }
    public function degree()          { return $this->belongsTo(Degree::class); }
    public function applicationtype() { return $this->belongsTo(Applicationtype::class); }
    public function user()            { return $this->belongsTo(User::class); }      // App\Models\User
    public function payment()         { return $this->hasOne(Payment::class); }
}
