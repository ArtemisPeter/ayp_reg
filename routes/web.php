<?php

use App\Http\Controllers\Admin\CircuitController;
use App\Http\Controllers\Admin\LocalChurchController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\YouthCampController;
use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\CampIdController;
use App\Http\Controllers\CircuitChurchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GateController;
use App\Http\Controllers\GateScanController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\RegistrationUploadController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);
Route::get('/privacy', fn () => view('privacy'));

Route::get('/template.csv', [TemplateController::class, 'template']);
Route::get('/churches.csv', [TemplateController::class, 'churches']);

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store']);

Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy']);
    Route::get('/password/change', [PasswordController::class, 'edit']);
    Route::post('/password/change', [PasswordController::class, 'update']);

    Route::middleware('password.changed')->group(function () {
        Route::get('/dashboard', DashboardController::class);

        Route::get('/attendees', [AttendeeController::class, 'index'])
            ->middleware('role:circuit_president,secretary,admin');

        Route::post('/registrations/upload', [RegistrationUploadController::class, 'store'])
            ->middleware('role:circuit_president,secretary,admin');

        Route::middleware('role:circuit_president')->group(function () {
            Route::get('/churches', [CircuitChurchController::class, 'index']);
            Route::post('/churches', [CircuitChurchController::class, 'store']);
        });

        Route::middleware('role:secretary,admin')->group(function () {
            Route::post('/attendees', [AttendeeController::class, 'store']);
            Route::get('/attendees/{attendee}/edit', [AttendeeController::class, 'edit']);
            Route::put('/attendees/{attendee}', [AttendeeController::class, 'update']);
            Route::delete('/attendees/{attendee}', [AttendeeController::class, 'destroy']);

            Route::get('/camp-ids', [CampIdController::class, 'index']);
            Route::get('/attendees/{attendee}/camp-id', [CampIdController::class, 'single']);
            Route::get('/camp-ids/{circuit}/fronts', [CampIdController::class, 'fronts']);
            Route::get('/camp-ids/{circuit}/backs', [CampIdController::class, 'backs']);
            Route::post('/camp-ids/{circuit}/printed', [CampIdController::class, 'markPrinted']);
        });

        Route::middleware('role:security,admin')->group(function () {
            Route::get('/gate', GateController::class);
            Route::get('/gate/roster.json', [GateScanController::class, 'roster']);
            Route::post('/gate/scans', [GateScanController::class, 'store']);
        });

        Route::middleware('role:secretary,admin')->group(function () {
            Route::get('/gate/review', [GateScanController::class, 'review']);
        });

        Route::middleware('role:tabulator,admin')->group(function () {
            Route::get('/events', [EventController::class, 'index']);
            Route::post('/events', [EventController::class, 'store']);
            Route::get('/events/{event}/score', [EventController::class, 'score']);
            Route::post('/events/{event}/score', [EventController::class, 'storeScore']);
        });

        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users', [UserController::class, 'store']);
            Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
            Route::delete('/users/{user}', [UserController::class, 'destroy']);

            Route::get('/youth-camps', [YouthCampController::class, 'index']);
            Route::post('/youth-camps', [YouthCampController::class, 'store']);
            Route::put('/youth-camps/{youthCamp}', [YouthCampController::class, 'update']);
            Route::post('/youth-camps/{youthCamp}/current', [YouthCampController::class, 'makeCurrent']);
            Route::delete('/youth-camps/{youthCamp}', [YouthCampController::class, 'destroy']);

            Route::get('/circuits', [CircuitController::class, 'index']);
            Route::post('/circuits', [CircuitController::class, 'store']);
            Route::put('/circuits/{circuit}', [CircuitController::class, 'update']);
            Route::delete('/circuits/{circuit}', [CircuitController::class, 'destroy']);

            Route::get('/circuits/{circuit}/churches', [LocalChurchController::class, 'index']);
            Route::post('/circuits/{circuit}/churches', [LocalChurchController::class, 'store']);
            Route::scopeBindings()->group(function () {
                Route::put('/circuits/{circuit}/churches/{localChurch}', [LocalChurchController::class, 'update']);
                Route::delete('/circuits/{circuit}/churches/{localChurch}', [LocalChurchController::class, 'destroy']);
            });
        });
    });
});
