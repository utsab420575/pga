<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applicant extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Existing relationships
    public function department()      { return $this->belongsTo(Department::class); }
    public function studenttype()     { return $this->belongsTo(Studenttype::class); }
    public function degree()          { return $this->belongsTo(Degree::class); }
    public function applicationtype() { return $this->belongsTo(Applicationtype::class); }
    public function user()            { return $this->belongsTo(User::class); }
    public function payment()         { return $this->hasOne(Payment::class); }

    // New relationships to your added tables
    public function basicInfo()           { return $this->hasOne(BasicInfo::class); }
    // in Applicant.php
    public function educationInfos()
    {
        return $this->hasMany(EducationInfo::class);
    }

    public function theses()              { return $this->hasMany(Thesis::class); }
    public function publications()        { return $this->hasMany(Publication::class); }
    public function jobExperiences()      { return $this->hasMany(JobExperience::class); }
    public function references()          { return $this->hasMany(Reference::class); }
    public function attachments()         { return $this->hasMany(Attachment::class); }
    public function eligibilityDegrees()  { return $this->hasMany(EligibilityDegree::class); }

    protected $casts = [
        'payment_status' => 'boolean',
        'edit_per'       => 'boolean',
    ];
}
