<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
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