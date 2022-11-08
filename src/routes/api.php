<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * @group No Auth APIs
 *
 * APIs that do not require User autherntication
 *
 * @subgroup Require Token APIs
 * @subgroup Sample Require Token APIs
 */

Route::prefix('change')->group(function () {
    Route::post('token', [Ikechukwukalu\Tokenmiddleware\Controllers\TokenController::class, 'changeToken'])->name('changeToken');
});
Route::post('token/required/{uuid}', [Ikechukwukalu\Tokenmiddleware\Controllers\TokenController::class, 'tokenRequired'])->name(config('tokenmiddleware.token.route', 'require_token'));

// Sample Novel APIs
Route::prefix('v1/sample/novels')->group(function () {
    Route::get('{id?}', [Ikechukwukalu\Tokenmiddleware\Controllers\NovelController::class, 'listNovels'])->name('listNovelsTest');

    // These APIs require a user's token before requests are processed
    Route::middleware(['require.token'])->group(function () {
        Route::post('/', [Ikechukwukalu\Tokenmiddleware\Controllers\NovelController::class, 'createNovel'])->name('createNovelTest');
        Route::patch('{id}', [Ikechukwukalu\Tokenmiddleware\Controllers\NovelController::class, 'updateNovel'])->name('updateNovelTest');
        Route::delete('{id}', [Ikechukwukalu\Tokenmiddleware\Controllers\NovelController::class, 'deleteNovel'])->name('deleteNovelTest');
    });
});
