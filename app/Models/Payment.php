<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'trxid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'paymentdate' => 'date',
        'amount'      => 'decimal:2', // safe even if DB column is DOUBLE
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
