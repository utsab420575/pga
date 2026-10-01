<?php
// app/Models/BasicInfo.php
namespace App\Models;

use App\Models\Concerns\LogsApplicantActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BasicInfo extends Model
{
    use HasFactory, LogsApplicantActivity;

    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
