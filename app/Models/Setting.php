<?php
// app/Models/Setting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_date'          => 'date',
        'end_date'            => 'date',
        'last_payment_date'   => 'date',
        'eligibility_last_date'=> 'date',
    ];
}
