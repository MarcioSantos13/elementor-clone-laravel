<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageBuilder\PageController;
use App\Http\Controllers\PageBuilder\ElementController;
use App\Http\Controllers\PageBuilder\RevisionController;
use App\Http\Controllers\PageBuilder\FormController;

Route::middleware(['web', 'auth'])->prefix('page-builder')->name('page-builder.')->group(function () {
    Route::resource('pages', PageController::class)->except(['show', 'edit']);
    Route::get('pages/{page}/editor', [PageController::class, 'edit'])->name('editor');
    Route::get('pages/{page}/render', [PageController::class, 'render'])->name('render');
    Route::get('pages/{page}/data', [PageController::class, 'getData'])->name('pages.data');
    Route::post('pages/{page}/publish', [PageController::class, 'publish'])->name('pages.publish');
    Route::post('pages/{page}/unpublish', [PageController::class, 'unpublish'])->name('pages.unpublish');
    Route::post('pages/{page}/duplicate', [PageController::class, 'duplicate'])->name('pages.duplicate');
    Route::get('pages/{page}/export', [PageController::class, 'export'])->name('pages.export');
    Route::post('pages/import', [PageController::class, 'import'])->name('pages.import');
    Route::get('templates', [PageController::class, 'listTemplates'])->name('templates.list');
    Route::post('pages/{page}/apply-template', [PageController::class, 'applyTemplate'])->name('pages.apply-template');
    Route::put('pages/{page}/layout', [PageController::class, 'updateLayout'])->name('pages.layout');

    Route::get('pages/{page}/elements', [ElementController::class, 'index'])->name('elements.index');
    Route::post('pages/{page}/elements', [ElementController::class, 'store'])->name('elements.store');
    Route::post('pages/{page}/elements/reorder', [ElementController::class, 'reorder'])->name('elements.reorder');
    Route::post('pages/{page}/elements/restore-snapshot', [ElementController::class, 'restoreSnapshot'])->name('elements.restore-snapshot');
    Route::get('elements/{element}', [ElementController::class, 'show'])->name('elements.show');
    Route::put('elements/{element}', [ElementController::class, 'update'])->name('elements.update');
    Route::delete('elements/{element}', [ElementController::class, 'destroy'])->name('elements.destroy');
    Route::post('elements/{element}/duplicate', [ElementController::class, 'duplicate'])->name('elements.duplicate');
    Route::post('elements/{element}/move', [ElementController::class, 'move'])->name('elements.move');
    Route::put('elements/{element}/settings', [ElementController::class, 'updateSettings'])->name('elements.settings');
    Route::put('elements/{element}/styles', [ElementController::class, 'updateStyles'])->name('elements.styles');
    Route::get('elements/{element}/render', [ElementController::class, 'renderElement'])->name('elements.render');
    Route::get('elements/{element}/controls', [ElementController::class, 'controls'])->name('elements.controls');
    Route::get('widgets/{type}/controls', [ElementController::class, 'widgetControls'])->name('widgets.controls');
    Route::post('upload', [ElementController::class, 'uploadImage'])->name('upload');

    Route::get('pages/{page}/revisions', [RevisionController::class, 'index'])->name('revisions.index');
    Route::get('revisions/{revision}', [RevisionController::class, 'show'])->name('revisions.show');
    Route::post('pages/{page}/revisions/{revision}/restore', [RevisionController::class, 'restore'])->name('revisions.restore');
    Route::get('pages/{page}/revisions/{revision}/diff', [RevisionController::class, 'diff'])->name('revisions.diff');
    Route::delete('revisions/{revision}', [RevisionController::class, 'destroy'])->name('revisions.destroy');
    Route::post('pages/{page}/revisions/prune', [RevisionController::class, 'prune'])->name('revisions.prune');
    Route::post('pages/{page}/revisions/auto-save', [RevisionController::class, 'autoSave'])->name('revisions.auto-save');

    Route::post('pages/{page}/form/submit', [FormController::class, 'submit'])->name('form.submit');
    Route::get('pages/{page}/form/submissions', [FormController::class, 'submissions'])->name('form.submissions');
});
