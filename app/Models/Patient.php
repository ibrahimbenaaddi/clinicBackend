<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

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
}
