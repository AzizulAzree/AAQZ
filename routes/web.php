<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseInspectorController;
use App\Http\Controllers\BppController;
use App\Http\Controllers\BppAppendixRowController;
use App\Http\Controllers\BppPdfExportController;
use App\Http\Controllers\BppPrintablePreviewController;
use App\Http\Controllers\BppQuotationExtractionController;
use App\Http\Controllers\BppSupplierQuoteController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StickyNoteController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? to_route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/entries', [DashboardController::class, 'store'])->name('dashboard.entries.store');
    Route::get('/bpp', [BppController::class, 'index'])->name('bpp.index');
    Route::post('/bpp', [BppController::class, 'store'])->name('bpp.store');
    Route::get('/bpp/{bpp}', [BppController::class, 'show'])->name('bpp.show');
    Route::put('/bpp/{bpp}', [BppController::class, 'update'])->name('bpp.update');
    Route::get('/bpp/{bpp}/export/pdf', [BppPdfExportController::class, 'export'])->name('bpp.export.pdf');
    Route::get('/bpp/{bpp}/printables/checklist', [BppPrintablePreviewController::class, 'checklist'])->name('bpp.printables.checklist');
    Route::get('/bpp/{bpp}/printables/page-one', [BppPrintablePreviewController::class, 'pageOne'])->name('bpp.printables.page-one');
    Route::get('/bpp/{bpp}/printables/page-two', [BppPrintablePreviewController::class, 'pageTwo'])->name('bpp.printables.page-two');
    Route::get('/bpp/{bpp}/printables/c1', [BppPrintablePreviewController::class, 'c1'])->name('bpp.printables.c1');
    Route::get('/bpp/{bpp}/printables/c2', [BppPrintablePreviewController::class, 'c2'])->name('bpp.printables.c2');
    Route::get('/bpp/{bpp}/printables/c3', [BppPrintablePreviewController::class, 'c3'])->name('bpp.printables.c3');
    Route::get('/bpp/{bpp}/printables/c4', [BppPrintablePreviewController::class, 'c4'])->name('bpp.printables.c4');
    Route::post('/bpp/{bpp}/quotation-extraction/parse', [BppQuotationExtractionController::class, 'parse'])->name('bpp.quotation-extraction.parse');
    Route::post('/bpp/{bpp}/quotation-extraction/apply', [BppQuotationExtractionController::class, 'apply'])->name('bpp.quotation-extraction.apply');
    Route::post('/bpp/{bpp}/appendix-rows', [BppAppendixRowController::class, 'store'])->name('bpp.appendix-rows.store');
    Route::put('/bpp/{bpp}/appendix-rows/{appendixRow}', [BppAppendixRowController::class, 'update'])->name('bpp.appendix-rows.update');
    Route::delete('/bpp/{bpp}/appendix-rows/{appendixRow}', [BppAppendixRowController::class, 'destroy'])->name('bpp.appendix-rows.destroy');
    Route::post('/bpp/{bpp}/supplier-quotes', [BppSupplierQuoteController::class, 'store'])->name('bpp.supplier-quotes.store');
    Route::put('/bpp/{bpp}/supplier-quotes/{supplierQuote}', [BppSupplierQuoteController::class, 'update'])->name('bpp.supplier-quotes.update');
    Route::delete('/bpp/{bpp}/supplier-quotes/{supplierQuote}', [BppSupplierQuoteController::class, 'destroy'])->name('bpp.supplier-quotes.destroy');
    Route::put('/bpp/{bpp}/supplier-quotes/{supplierQuote}/select', [BppSupplierQuoteController::class, 'select'])->name('bpp.supplier-quotes.select');
    Route::put('/sticky-note', [StickyNoteController::class, 'update'])->name('sticky-note.update');
    Route::get('/project', [ProjectController::class, 'index'])->name('project.index');
    Route::post('/project/workspaces', [ProjectController::class, 'storeWorkspace'])->name('project.workspaces.store');
    Route::post('/project/nodes', [ProjectController::class, 'storeNode'])->name('project.nodes.store');
    Route::get('/project/shortcuts/{workspaceNode}', [ProjectController::class, 'openShortcut'])->name('project.shortcuts.open');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::middleware('admin')->group(function () {
        Route::get('/admin/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/admin/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/admin/database', [DatabaseInspectorController::class, 'index'])->name('database.index');
        Route::get('/admin/database/{table}', [DatabaseInspectorController::class, 'show'])->name('database.show');
        Route::put('/admin/database/{table}', [DatabaseInspectorController::class, 'update'])->name('database.update');
        Route::delete('/admin/database/{table}', [DatabaseInspectorController::class, 'destroy'])->name('database.destroy');
    });
});

require __DIR__.'/auth.php';
