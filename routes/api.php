<?php

use App\Http\Controllers\DoctorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(DoctorController::class)->group(function (){
    Route::prefix('doctors')->group(function(){
        Route::get('/','index');
        Route::get('/{doctorId}','show')->where('doctorId', '[0-9]+');
        Route::post('/','store');
        Route::patch('/update/{doctorId}','update')->where('doctorId', '[0-9]+');
        Route::delete('/delete/{doctorId}','destroy')->where('doctorId', '[0-9]+');
    });
});

