<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentRecordController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\ParentProfileController;
use App\Http\Controllers\GuidanceProfileController;
use App\Http\Controllers\CoachingController;
use App\Http\Controllers\GoodMoralController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeacherProfileController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/create/studentviolation',[StudentRecordController::class,'create']);

Route::get('/users', [ApiController::class, 'getUsers']);
Route::post('/login', [AuthController::class, 'login'])->name('login');



Route::resource('studentrecord',StudentRecordController::class)->except(['index','update']);

// Route::prefix('api')->group(function () {
//     Route::get('/goodmorals', [GoodMoralController::class, 'index']);
//     Route::post('/goodmorals', [GoodMoralController::class, 'store']);
// });
// Route::middleware('auth:api')->get('/goodmorals', [GoodMoralController::class, 'index']);
// Route::middleware('auth:api')->post('/goodmorals/store', [GoodMoralController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    Route::get('/goodmorals', [GoodMoralController::class, 'index']);
    Route::post('/goodmorals/create', [GoodMoralController::class, 'create']);
    Route::delete('/goodmorals/delete', [GoodMoralController::class, 'delete']);


});
Route::middleware('auth:api')->get('/dashboard', [DashboardController::class, 'dashboard']);
Route::middleware('auth:api')->get('/dashboard2', [DashboardController::class, 'dashboard2']);



Route::group(['middleware' => ['api', 'auth', 'checkRole:student']], function ($router) {
    Route::get('/createcoaching', [CoachingController::class,'create']);
    Route::post('/store/coaching',[CoachingController::class, 'store']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::middleware('auth:api')->group(function () {

Route::get('/profile', [StudentProfileController::class, 'profile']);
});
Route::middleware('auth:api')->group(function () {
    Route::get('/profileparent', [ParentProfileController::class, 'profileparent']);
});
Route::middleware('auth:api')->group(function () {
    Route::get('/profileteacher', [TeacherProfileController::class, 'profileteacher']);
});
Route::middleware(['auth'])->put('/update', [StudentProfileController::class, 'update']);
Route::middleware(['auth'])->put('/updateparent', [ParentProfileController::class, 'updateparent']);
Route::middleware(['auth'])->put('/updateteacher', [TeacherProfileController::class, 'updateteacher']);

Route::get('/guidanceprofile', [GuidanceProfileController::class, 'guidanceprofile']);
