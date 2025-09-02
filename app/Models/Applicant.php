<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applicant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'payment_status' => 'boolean',
        'edit_per'       => 'boolean',
    ];

    public function department()      { return $this->belongsTo(Department::class); }
    public function studenttype()     { return $this->belongsTo(Studenttype::class); }
    public function degree()          { return $this->belongsTo(Degree::class); }
    public function applicationtype() { return $this->belongsTo(Applicationtype::class); }
    public function user()            { return $this->belongsTo(User::class); }
    public function payment()         { return $this->hasOne(Payment::class); }
}
