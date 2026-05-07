<?php

namespace App\Models;

class Doctor extends User
{
    protected $table = 'doctors';
    protected $primaryKey = 'doctor_id';
    public $incrementing = false;

    protected $fillable = [
        'doctor_id',
        'specialization',
        'license_number',
        'phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }

    public function appointment()
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'doctor_id');
    }

    public function appointmentSlots()
    {
        return $this->hasMany(AppointmentSlot::class, 'doctor_id', 'doctor_id');
    }
}
