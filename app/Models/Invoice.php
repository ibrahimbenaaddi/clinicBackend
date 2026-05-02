<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';
    protected $fillable = [
        'appointment_id',
        'amount',
        'invoice_date',
        'status',
        'payment_method'
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'datetime',
        ];
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }
}
