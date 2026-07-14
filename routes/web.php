<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\DataAkunController;
use App\Http\Controllers\Admin\KanwilController;
use App\Http\Controllers\Admin\UptController;

use App\Http\Controllers\Admin\JenisGolonganController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FileStreamController;
use App\Http\Controllers\DirectMessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ChannelController;
use App\Http\Controllers\Admin\CorporateHighlightController;
use App\Services\WsoApiService;
use App\Services\SdpMasterService;

Route::get('/test-kanwil', function (SdpMasterService $sdp) {

    return response()->json(
        $sdp->getKanwil()
    );

});

Route::get('/test-upt', function (SdpMasterService $sdp) {

    return response()->json(
        $sdp->getUpt()
    );

});

// Route::get('/test-kanwil', function (WsoApiService $api) {

//     dd($api->getKanwilLengkap());

// });

// Route::get('/test-upt', function (WsoApiService $api) {

//     dd($api->getUptLengkap());

// });

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('direct-messages', [DirectMessageController::class, 'index'])->name('direct-messages');
    Route::post('posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('posts/{post}/react', [PostController::class, 'toggleReaction'])->name('posts.react');
    Route::post('posts/{post}/comments', [PostController::class, 'storeComment'])->name('posts.comments.store');
    Route::put('comments/{comment}', [PostController::class, 'updateComment'])->name('comments.update');
    Route::delete('comments/{comment}', [PostController::class, 'destroyComment'])->name('comments.destroy');

    // Profile Pages
    Route::get('profile/{user?}', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar.upload');
    Route::post('profile/banner', [ProfileController::class, 'uploadBanner'])->name('profile.banner.upload');

    // Direct Messages API
    Route::get('api/direct-messages/contacts', [DirectMessageController::class, 'getContacts']);
    Route::get('api/direct-messages/history/{user}', [DirectMessageController::class, 'getHistory']);
    Route::post('api/direct-messages/send', [DirectMessageController::class, 'sendMessage']);
    Route::post('api/direct-messages/read/{sender}', [DirectMessageController::class, 'markAsRead']);
});
Route::get('/view-file', [FileStreamController::class, 'show'])->name('view.file');

Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    //Controller Data Master
    Route::resource('roles', RoleController::class);
    Route::resource('data-akun', DataAkunController::class)->names('data-akun');
    Route::resource('kanwils', KanwilController::class);
    Route::resource('upts', UptController::class);
    Route::resource('jenis-golongans', JenisGolonganController::class);
    Route::resource('channels', ChannelController::class);
    Route::resource('corporate-highlights', CorporateHighlightController::class)->names('corporate-highlights');
    

});

require __DIR__ . '/settings.php';
