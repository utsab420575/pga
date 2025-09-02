<?php
// app/Models/JobExperience.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobExperience extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'from' => 'date',   // kept as provided
        'to'   => 'date',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
