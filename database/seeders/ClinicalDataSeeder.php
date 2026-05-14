<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ClinicalDataSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = Doctor::all();
        $patients = Patient::all();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            return;
        }

        foreach ($doctors as $doctor) {
            // Create some slots for each doctor
            for ($i = 0; $i < 5; $i++) {
                $start = Carbon::now()->addDays(rand(1, 10))->setHour(rand(9, 16))->setMinute(0)->setSecond(0);
                $slot = AppointmentSlot::create([
                    'doctor_id' => $doctor->doctor_id,
                    'start_time' => $start,
                    'end_time' => $start->copy()->addMinutes(30),
                    'max_patients' => 1,
                    'status' => 'available'
                ]);

                // Randomly book some slots
                if (rand(0, 1)) {
                    $patient = $patients->random();
                    $appointment = Appointment::create([
                        'patient_id' => $patient->patient_id,
                        'doctor_id' => $doctor->doctor_id,
                        'slot_id' => $slot->slot_id,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'status' => 'confirmed',
                        'reason_for_visit' => 'Regular checkup'
                    ]);

                    // Create a medical record for some appointments
                    if (rand(0, 1)) {
                        $record = MedicalRecord::create([
                            'appointment_id' => $appointment->appointment_id,
                            'diagnosis_code' => 'Z00.00',
                            'symptoms' => 'Routine visit',
                            'clinical_notes' => 'Patient is in good health.'
                        ]);

                        // Create a prescription
                        Prescription::create([
                            'record_id' => $record->record_id,
                            'medication_name' => 'Multivitamins',
                            'instructions' => 'Take one tablet daily.'
                        ]);
                    }

                    // Create an invoice
                    Invoice::create([
                        'appointment_id' => $appointment->appointment_id,
                        'amount' => rand(50, 200),
                        'invoice_date' => Carbon::now(),
                        'payment_method' => 'card',
                        'status' => rand(0, 1) ? 'paid' : 'pending'
                    ]);
                }
            }
        }
    }
}
