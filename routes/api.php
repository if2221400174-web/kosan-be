<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\GaleriController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\KamarFasilitasController;
use App\Http\Controllers\KontrakSewaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PenyewaController;
use App\Http\Controllers\ProfilKosController;
use App\Http\Controllers\TagihanController;
use Illuminate\Support\Facades\Route;

// PUBLIC ROUTES (Authentication & Landing Page Kos)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/auth/google', [AuthController::class, 'googleAuth']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/public/profil-kos', [ProfilKosController::class, 'index']);
Route::get('/public/kamar', [KamarController::class, 'index']);
Route::get('/public/kamar/{id}', [KamarController::class, 'show']);
Route::get('/public/fasilitas', [FasilitasController::class, 'index']);
Route::get('/public/galeri', [GaleriController::class, 'index']);

// PROTECTED ROUTES
Route::middleware(['auth:api'])->group(function () {
    // PROFILE
    Route::get('/profile', function () {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();
        return response()->json($user->load('penyewa'));
    });

    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // 1. AKSES SEMUA ROLE (Administrator, Admin/Pekerja, & Penyewa)
    Route::middleware(['role:administrator,admin,penyewa'])->group(function () {
        Route::apiResource('/profil-kos', ProfilKosController::class)->only(['index', 'show']);
        Route::apiResource('/kamar', KamarController::class)->only(['index', 'show']);
        Route::apiResource('/fasilitas', FasilitasController::class)->only(['index', 'show']);
        Route::apiResource('/kamar-fasilitas', KamarFasilitasController::class)->only(['index', 'show']);
        Route::apiResource('/galeri', GaleriController::class)->only(['index', 'show']);
        Route::apiResource('/pengumuman', PengumumanController::class)->only(['index', 'show']);

        Route::apiResource('/penyewa', PenyewaController::class)->only(['index', 'show', 'store', 'update']);
        Route::apiResource('/kontrak-sewa', KontrakSewaController::class)->only(['index', 'show', 'store']);
        Route::apiResource('/tagihan', TagihanController::class)->only(['index', 'show']);
        Route::apiResource('/pembayaran', PembayaranController::class)->only(['index', 'show', 'store']);
        Route::apiResource('/laporan', LaporanController::class)->only(['index', 'show', 'store']);
        Route::get('/chat', [ChatController::class, 'index']);
        Route::post('/chat', [ChatController::class, 'store']);
    });

    // 2. AKSES OPERASIONAL PENGELOLA (Administrator & Admin/Pekerja Kos)
    // Pekerja kos bisa tambah/edit data operasional dan verifikasi pembayaran, tapi TIDAK BISA hapus data keuangan/master krusial
    Route::middleware(['role:administrator,admin'])->group(function () {
        Route::apiResource('/kamar', KamarController::class)->only(['store', 'update']);
        Route::apiResource('/fasilitas', FasilitasController::class)->only(['store', 'update']);
        Route::apiResource('/kamar-fasilitas', KamarFasilitasController::class)->only(['store', 'destroy']);
        Route::apiResource('/galeri', GaleriController::class)->only(['store', 'update', 'destroy']);

        Route::apiResource('/kontrak-sewa', KontrakSewaController::class)->only(['update']);
        Route::apiResource('/tagihan', TagihanController::class)->only(['store', 'update']);
        Route::apiResource('/pembayaran', PembayaranController::class)->only(['update']);
        Route::apiResource('/laporan', LaporanController::class)->only(['update']);
        Route::apiResource('/pengumuman', PengumumanController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('/pengeluaran', PengeluaranController::class)->only(['index', 'show', 'store', 'update']);
    });

    // 3. SUPER AKSES KHUSUS PEMILIK (Hanya Administrator)
    // Mengelola akun user/admin, mengubah profil & nomor rekening kos, serta menghapus data krusial/keuangan
    Route::middleware(['role:administrator'])->group(function () {
        Route::apiResource('/users', AuthController::class);
        Route::apiResource('/profil-kos', ProfilKosController::class)->only(['store', 'update', 'destroy']);

        Route::apiResource('/penyewa', PenyewaController::class)->only(['destroy']);
        Route::apiResource('/kamar', KamarController::class)->only(['destroy']);
        Route::apiResource('/fasilitas', FasilitasController::class)->only(['destroy']);
        Route::apiResource('/kontrak-sewa', KontrakSewaController::class)->only(['destroy']);
        Route::apiResource('/tagihan', TagihanController::class)->only(['destroy']);
        Route::apiResource('/pembayaran', PembayaranController::class)->only(['destroy']);
        Route::apiResource('/laporan', LaporanController::class)->only(['destroy']);
        Route::apiResource('/pengeluaran', PengeluaranController::class)->only(['destroy']);
    });
});
