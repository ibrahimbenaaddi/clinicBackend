<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthDoctorController;
use App\Http\Controllers\Auth\AuthPatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use Illuminate\Support\Facades\Route;

Route::controller(DoctorController::class)->group(function () {
    Route::prefix('doctors')->group(function () {
        Route::get('/', 'index');
        Route::get('/{doctorId}', 'show')->where('doctorId', '[0-9]+');
        Route::post('/', 'store');
        Route::patch('/{doctorId}', 'update')->where('doctorId', '[0-9]+');
        Route::delete('/{doctorId}', 'destroy')->where('doctorId', '[0-9]+');
    });
});

Route::controller(PatientController::class)->group(function () {
    Route::prefix('patients')->group(function () {
        Route::get('/', 'index');
        Route::get('/{patientId}', 'show')->where('patientId', '[0-9]+');
        Route::post('/', 'store');
        Route::patch('/{patientId}', 'update')->where('patientId', '[0-9]+');
        Route::delete('/{patientId}', 'destroy')->where('patientId', '[0-9]+');
    });
});

Route::controller(AppointmentController::class)->group(function () {
    Route::prefix('appointments')->group(function () {
        Route::get('/', 'index');
        Route::get('/{appointmentId}', 'show')->where('appointmentId', '[0-9]+');
        Route::post('/', 'store');
        Route::patch('/{appointmentId}', 'update')->where('appointmentId', '[0-9]+');
        Route::delete('/{appointmentId}', 'destroy')->where('appointmentId', '[0-9]+');
    });
});

Route::controller(MedicalRecordController::class)->group(function () {
    Route::prefix('records')->group(function () {
        Route::get('/', 'index');
        Route::get('/{recordId}', 'show')->where('recordId', '[0-9]+');
        Route::post('/', 'store');
        Route::patch('/{recordId}', 'update')->where('recordId', '[0-9]+');
        Route::delete('/{recordId}', 'destroy')->where('recordId', '[0-9]+');
    });
});

Route::controller(PrescriptionController::class)->group(function () {
    Route::prefix('prescriptions')->group(function () {
        Route::get('/', 'index');
        Route::get('/{prescriptionId}', 'show')->where('prescriptionId', '[0-9]+');
        Route::post('/', 'store');
        Route::patch('/{prescriptionId}', 'update')->where('prescriptionId', '[0-9]+');
        Route::delete('/{prescriptionId}', 'destroy')->where('prescriptionId', '[0-9]+');
    });
});

Route::controller(InvoiceController::class)->group(function () {
    Route::prefix('invoices')->group(function () {
        Route::get('/', 'index');
        Route::get('/{invoiceId}', 'show')->where('invoiceId', '[0-9]+');
        Route::post('/', 'store');
        Route::patch('/{invoiceId}', 'update')->where('invoiceId', '[0-9]+');
        Route::delete('/{invoiceId}', 'destroy')->where('invoiceId', '[0-9]+');
    });
});

Route::controller(AuthPatientController::class)->group(function () {
    Route::prefix('patients')->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
    });
});

Route::controller(AuthDoctorController::class)->group(function () {
    Route::prefix('doctors')->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
    });
});

Route::middleware('isPatient')->group(function () {
    Route::prefix('patient')->group(function () {
        Route::post('/logout', [AuthPatientController::class, 'logout']);

        Route::controller(PatientController::class)->group(function () {
            Route::get('/profile/{patientId}', 'show')->where('patientId', '[0-9]+');
            Route::patch('/profile/{patientId}', 'update')->where('patientId', '[0-9]+');
        });
        Route::controller(AppointmentController::class)->group(function () {
            // the appointments containe record bc relation one to one and invoices and prescriptions
            Route::get('/{patientId}/appointments', 'getAllByPatient')->where('patientId', '[0-9]+');
            Route::get('/appointments/{appointmentId}', 'show')->where('appointmentId', '[0-9]+');
            Route::post('/appointments', 'store');
            Route::patch('/{patientId}/appointments/{appointmentId}/cancel', 'cancelAppointment')->where(['patientId' => '[0-9]+', 'appointmentId' => '[0-9]+']);
        });
        Route::get('/invoices/{invoiceId}', [InvoiceController::class, 'show'])->where('patientId', '[0-9]+');
        Route::get('/records/{recordId}', [MedicalRecordController::class, 'show'])->where('recordId', '[0-9]+');
        Route::get('/prescriptions/{prescriptionId}', [PrescriptionController::class, 'show'])->where('prescriptionId', '[0-9]+');
        Route::controller(DoctorController::class)->group(function () {
            Route::prefix('doctors')->group(function () {
                Route::get('/', 'index');
                Route::get('/{doctorId}', 'show')->where('doctorId', '[0-9]+');
            });
        });
    });
});

Route::middleware('isDoctor')->group(function () {

    Route::prefix('doctor')->group(function () {
        Route::post('/logout', [AuthDoctorController::class, 'logout']);
        Route::controller(DoctorController::class)->group(function () {
            Route::get('/profile/{doctorId}', 'show')->where('doctorId', '[0-9]+');
            Route::patch('/profile/{doctorId}', 'update')->where('doctorId', '[0-9]+');
        });
        Route::controller(AppointmentController::class)->group(function () {
            // the appointments containe record bc relation one to one and invoices and prescriptions 
            Route::get('/{doctorId}/appointments', 'getAllByDoctor')->where('doctorId', '[0-9]+');
            Route::get('/appointments/{appointmentId}', 'show')->where('appointmentId', '[0-9]+');
            Route::patch('/{doctorId}/appointments/{appointmentId}/status', 'updateStatus')->where(['doctorId' => '[0-9]+', 'appointmentId' => '[0-9]+']);
            Route::get('/patients/{patientId}/appointments', 'getAllByPatient')->where('patientId', '[0-9]+'); // get records and invoices and prescription for patient
        });
        Route::controller(MedicalRecordController::class)->group(function () {
            Route::prefix('records')->group(function () {
                Route::get('/{recordId}', 'show')->where('recordId', '[0-9]+');
                Route::post('/', 'store');
                Route::patch('/{recordId}', 'update')->where('recordId', '[0-9]+');
                Route::delete('/{recordId}', 'destroy')->where('recordId', '[0-9]+');
            });
        });
        Route::controller(PrescriptionController::class)->group(function () {
            Route::get('/{doctorId}/prescriptions', 'getAllByDoctor')->where('doctorId', '[0-9]+');
            Route::prefix('prescriptions')->group(function () {
                Route::post('/', 'store');
                Route::get('/{prescriptionId}', 'show')->where('prescriptionId', '[0-9]+');
                Route::patch('/{prescriptionId}', 'update')->where('prescriptionId', '[0-9]+');
                Route::delete('/{prescriptionId}', 'destroy')->where('prescriptionId', '[0-9]+');
            });
        });
        Route::controller(PatientController::class)->group(function () {
            Route::prefix('patients')->group(function () {
                Route::get('/', 'index');
                Route::get('/{patientId}', 'show')->where('patientId', '[0-9]+');
            });
        });
    });
});
