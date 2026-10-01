<?php
// app/Models/EducationInfo.php
namespace App\Models;

use App\Models\Concerns\LogsApplicantActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EducationInfo extends Model
{
    use HasFactory, LogsApplicantActivity;

    protected $guarded = [];

    protected $casts = [
        'year_of_passing' => 'integer',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}