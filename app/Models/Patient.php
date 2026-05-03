<?php

namespace App\Models;

class Patient extends User
{
    protected $table = 'patients';
    protected $primaryKey = 'patient_id';
    public $incrementing = false;

    protected $fillable = [
        'patient_id',
        'date_birth',
        'phone',
        'address',
        'insurance_info'
    ];

    protected function casts(): array
    {
        return [
            'date_birth' => 'date'
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'patient_id', 'user_id');
    }

    public function appointment()
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'patient_id');
    }
}
