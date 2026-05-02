<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prescriptions';
    protected $primaryKey = 'prescription_id';
    protected $fillable = [
        'record_id',
        'medication_name',
        'instructions'
    ];

    public function record()
    {
        return $this->belongsTo(MedicalRecord::class, 'record_id', 'record_id');
    }
}
