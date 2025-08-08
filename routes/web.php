<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsulanController;
use App\Http\Controllers\PersetujuanController;
use App\Http\Controllers\LocationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sini kamu bisa mendefinisikan semua route web untuk aplikasi SIGAP.
| Routes ini diproses oleh RouteServiceProvider dan menggunakan group "web".
|
*/

Route::get('/', function () {
       return redirect()->route('login');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// ✅ Semua route di bawah ini hanya bisa diakses jika sudah login
Route::middleware(['auth'])->group(function () {
    // Form Usulan
    Route::get('/usulan/create', [UsulanController::class, 'create'])->name('usulan.create');
    Route::post('/usulan/store', [UsulanController::class, 'store'])->name('usulan.store');
    Route::post('/usulan/setujui', [UsulanController::class, 'setujui'])->name('usulan.setujui');

    // Persetujuan
    Route::get('/persetujuan', [PersetujuanController::class, 'index'])->name('persetujuan.index');
    Route::post('/persetujuan/{id}/setujui', [PersetujuanController::class, 'setujui'])->name('persetujuan.setujui');
    Route::post('/persetujuan/{id}/tolak', [PersetujuanController::class, 'tolak'])->name('persetujuan.tolak');

    // ✅ AJAX route untuk dropdown lokasi
    Route::get('/get-lantai', [LocationController::class, 'getLantai']);
    Route::get('/get-ruang/{lantai_id}', [LocationController::class, 'getRuang']);
    Route::get('/get-subruang/{ruang_id}', [LocationController::class, 'getSubRuang']);
});
