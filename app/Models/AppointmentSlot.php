<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentSlot extends Model
{
    use SoftDeletes;

    protected $table = 'appointment_slots';
    protected $primaryKey = 'slot_id';

    protected $fillable = [
        'doctor_id',
        'start_time',
        'end_time',
        'status',
        'booked_count',
        'max_patients'
    ];

    protected function casts(): array
    {
        return [
            'booked_count' => 'integer',
            'max_patients' => 'integer',
            'start_time' => 'datetime',
            'end_time'   => 'datetime',
        ];
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'slot_id', 'slot_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function (AppointmentSlot $slot) {
            if ($slot->booked_count >= $slot->max_patients) {
                $slot->status = 'full';
            }
            
            if ($slot->booked_count < $slot->max_patients && $slot->getOriginal('status') === 'full') {
                $slot->status = 'available';
            }
        });
    }
}
