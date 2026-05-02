<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $table = 'medical_records';
    protected $primaryKey = 'record_id';

    protected $fillable = [
        'appointment_id',
        'diagnosis_code',
        'clinical_notes',
        'symptoms',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'record_id', 'record_id');
    }
}
