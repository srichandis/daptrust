<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EntryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\TransparencyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::prefix('admin')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('admin.login.submit');

    Route::get('forgot-password', [AuthController::class, 'showForgotForm'])->name('admin.forgot');
    Route::post('forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:5,1')->name('admin.forgot.submit');
    Route::get('reset-password', [AuthController::class, 'showResetForm'])->name('admin.reset');
    Route::post('reset-password', [AuthController::class, 'reset'])->name('admin.reset.submit');

    Route::middleware('admin')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');

        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('reports', [ReportController::class, 'index'])->name('admin.reports.index');

        Route::get('entries/export', [EntryController::class, 'export'])->name('admin.entries.export');
        Route::resource('entries', EntryController::class, [
            'as' => 'admin',
            'parameters' => ['entries' => 'entry'],
        ])->except('show');

        Route::resource('accounts', AccountController::class, [
            'as' => 'admin',
            'parameters' => ['accounts' => 'account'],
        ])->except('show');

        Route::controller(TransparencyController::class)->prefix('transparency')->name('admin.transparency.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('introduction', 'introduction')->name('introduction');
            Route::get('financial-report', 'financialReport')->name('financial-report');
            Route::get('trustees', 'trustees')->name('trustees');
            Route::get('notice-board', 'noticeBoard')->name('notice-board');
            Route::get('endowment', 'endowment')->name('endowment');
            Route::get('donors', 'donors')->name('donors');
            Route::get('donation-distribution', 'donationDistribution')->name('donation-distribution');
            Route::get('donation-particulars', 'donationParticulars')->name('donation-particulars');
            Route::get('resolutions', 'resolutions')->name('resolutions');
            Route::get('certificates', 'certificates')->name('certificates');
            Route::get('mf', 'mf')->name('mf');
            Route::get('fd', 'fd')->name('fd');
            Route::get('rd', 'rd')->name('rd');
            Route::get('it-return', 'itReturn')->name('it-return');
            Route::get('ops', 'ops')->name('ops');
            Route::get('audit-report', 'auditReport')->name('audit-report');
            Route::get('bank-particulars', 'bankParticulars')->name('bank-particulars');
            Route::get('income-trend', 'incomeTrend')->name('income-trend');
            Route::get('donation-expense-trend', 'donationExpenseTrend')->name('donation-expense-trend');
            Route::get('investment-trend', 'investmentTrend')->name('investment-trend');
            Route::get('it-return-trend', 'itReturnTrend')->name('it-return-trend');
            Route::get('ops-trend', 'opsTrend')->name('ops-trend');
        });
    });
});
