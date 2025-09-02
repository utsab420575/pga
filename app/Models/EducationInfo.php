<?php
// app/Models/EducationInfo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EducationInfo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'year_of_passing' => 'integer',
        'cgpa'            => 'decimal:2',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
