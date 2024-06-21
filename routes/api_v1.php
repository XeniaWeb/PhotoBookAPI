<?php

use App\Http\Controllers\v1\AlbumController;
use App\Http\Controllers\v1\AuthorController;
use App\Http\Controllers\v1\CommentController;
use App\Http\Controllers\v1\PhotoController;
use App\Http\Controllers\v1\SocialController;
use App\Http\Controllers\v1\UploadFilesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/photos', PhotoController::class);
    Route::apiResource('/albums', AlbumController::class);
    Route::apiResource('/authors', AuthorController::class);
    Route::apiResource('/comments', CommentController::class);
    Route::apiResource('/socials', SocialController::class);

    Route::post('authors/upload_avatar', [UploadFilesController::class, 'uploadAvatar']);
    Route::post('authors/upload_cover', [UploadFilesController::class, 'uploadCover']);
    Route::post('albums/{id}', [AlbumController::class, 'update']);
    Route::post('authors/{id}', [AuthorController::class, 'update']);
    Route::post('socials/add', [SocialController::class, 'addSocialToProfile']);
    Route::post('socials/{id}/update', [SocialController::class, 'updateSocialInProfile']);
    Route::post('socials/{id}/delete', [SocialController::class, 'deleteSocialFromProfile']);

    Route::post('photos/{id}/likes', [PhotoController::class, 'toggleLike']);
});
