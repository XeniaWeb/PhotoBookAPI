<?php

use App\Http\Controllers\v1\AlbumController;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\AuthorController;
use App\Http\Controllers\v1\CommentController;
use App\Http\Controllers\v1\PhotoController;
use App\Http\Controllers\v1\SocialController;
use App\Http\Controllers\v1\UploadFilesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'registerUserApi']);
Route::post('/login', [AuthController::class, 'loginApi']);
Route::post('/logout', [AuthController::class, 'logoutApi'])->middleware('auth:sanctum');

Route::apiResource('/v1/photos', PhotoController::class);
Route::apiResource('/v1/albums', AlbumController::class);
Route::apiResource('/v1/authors', AuthorController::class);
Route::apiResource('/v1/comments', CommentController::class);
Route::apiResource('/v1/socials', SocialController::class);

Route::post('v1/authors/upload_avatar', [UploadFilesController::class, 'uploadAvatar']);
Route::post('v1/authors/upload_cover', [UploadFilesController::class, 'uploadCover']);
Route::post('/v1/albums/{id}', [AlbumController::class, 'update']);
Route::post('/v1/authors/{id}', [AuthorController::class, 'update']);
Route::post('/v1/socials/add', [SocialController::class, 'addSocialToProfile']);
Route::post('/v1/socials/{id}/update', [SocialController::class, 'updateSocialInProfile']);
Route::post('/v1/socials/{id}/delete', [SocialController::class, 'deleteSocialFromProfile']);

Route::post('/v1/photos/{id}/likes', [PhotoController::class, 'toggleLike']);
