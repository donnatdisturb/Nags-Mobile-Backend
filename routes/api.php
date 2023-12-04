<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentRecordController;
use App\Http\Controllers\CoachingController;
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



Route::get('/users', [ApiController::class, 'getUsers']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/loginOTP',[AuthController::class,'loginOTP']);
Route::post('/verifyOtp',[AuthController::class,'verifyOtp']);
Route::post('/profile', [AuthController::class, 'profile']);
Route::resource('studentrecord',StudentRecordController::class)->except(['index','update','store']);

Route::group(['middleware' => ['api','auth','checkRole:teacher']],function($router){
    Route::get('/create/studentviolationteacher',[StudentRecordController::class,'create1']);
    Route::get('/getFilteredStudents',[StudentRecordController::class,'getFilteredStudents']);
    Route::post('/store/studentviolationteacher',[StudentRecordController::class,'store1']);
    Route::post('/logoutteacher', [AuthController::class, 'logoutteacher']);
});

Route::group(['middleware'=>['api','auth','checkRole:parent']],function($router){
    Route::get('/create/CoachingParent',[CoachingController::class,'create2']);
    Route::post('/store/coachingParent',[CoachingController::class, 'store2']);
    Route::post('/logoutparent', [AuthController::class, 'logoutparent']);
    Route::get('/fetchrecords/parents',[CoachingController::class,'parentrecords']);
    Route::get('/checkRecords',[CoachingController::class,'checkrecords']);
    Route::delete('/cancel-transactionParent/{id}', [CoachingController::class, 'cancelTransactionParent']);
});
 

Route::group(['middleware' => ['api', 'auth', 'checkRole:student']], function ($router) {
    Route::get('/create/studentviolation',[StudentRecordController::class,'create']);
    Route::post('/store/studentviolation',[StudentRecordController::class,'store']);
    Route::get('/createcoaching', [CoachingController::class,'create']);
    Route::post('/store/coaching',[CoachingController::class, 'store']);
    Route::get('/fetchrecords',[CoachingController::class,'records']);
    Route::delete('/cancel-transaction/{id}', [CoachingController::class, 'cancelTransaction']);
    Route::get('/checkRecords',[CoachingController::class,'checkrecords']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


