<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use Illuminate\Support\Facades\Route;

Route::controller(DoctorController::class)->group(function (){
    Route::prefix('doctors')->group(function(){
        Route::get('/','index');
        Route::get('/{doctorId}','show')->where('doctorId', '[0-9]+');
        Route::post('/','store');
        Route::patch('/{doctorId}','update')->where('doctorId', '[0-9]+');
        Route::delete('/{doctorId}','destroy')->where('doctorId', '[0-9]+');
    });
});

Route::controller(PatientController::class)->group(function (){
    Route::prefix('patients')->group(function(){
        Route::get('/','index');
        Route::get('/{patientId}','show')->where('patientId', '[0-9]+');
        Route::post('/','store');
        Route::patch('/{patientId}','update')->where('patientId', '[0-9]+');
        Route::delete('/{patientId}','destroy')->where('patientId', '[0-9]+');
    });
});

Route::controller(AppointmentController::class)->group(function (){
    Route::prefix('appointments')->group(function(){
        Route::get('/','index');
        Route::get('/{appointmentId}','show')->where('appointmentId', '[0-9]+');
        Route::post('/','store');
        Route::patch('/{appointmentId}','update')->where('appointmentId', '[0-9]+');
        Route::delete('/{appointmentId}','destroy')->where('appointmentId', '[0-9]+');
    });
});

Route::controller(MedicalRecordController::class)->group(function (){
    Route::prefix('records')->group(function(){
        Route::get('/','index');
        Route::get('/{recordId}','show')->where('recordId', '[0-9]+');
        Route::post('/','store');
        Route::patch('/{recordId}','update')->where('recordId', '[0-9]+');
        Route::delete('/{recordId}','destroy')->where('recordId', '[0-9]+');
    });
});

Route::controller(PrescriptionController::class)->group(function (){
    Route::prefix('prescriptions')->group(function(){
        Route::get('/','index');
        Route::get('/{prescriptionId}','show')->where('prescriptionId', '[0-9]+');
        Route::post('/','store');
        Route::patch('/{prescriptionId}','update')->where('prescriptionId', '[0-9]+');
        Route::delete('/{prescriptionId}','destroy')->where('prescriptionId', '[0-9]+');
    });
});

Route::controller(InvoiceController::class)->group(function (){
    Route::prefix('invoices')->group(function(){
        Route::get('/','index');
        Route::get('/{invoiceId}','show')->where('invoiceId', '[0-9]+');
        Route::post('/','store');
        Route::patch('/{invoiceId}','update')->where('invoiceId', '[0-9]+');
        Route::delete('/{invoiceId}','destroy')->where('invoiceId', '[0-9]+');
    });
});