<?php
// app/Models/EligibilityDegree.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EligibilityDegree extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'cgpa'            => 'decimal:2',
        'date_graduation' => 'date',
        'total_credit'    => 'decimal:2',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
