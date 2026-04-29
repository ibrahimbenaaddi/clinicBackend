<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

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
}
