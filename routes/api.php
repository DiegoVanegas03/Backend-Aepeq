<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PromotoresController;
use App\Http\Controllers\Api\ComitesController;
use App\Http\Controllers\Api\TalleresController;
use App\Http\Controllers\Api\ProgramaController;
use App\Http\Controllers\Api\ActividadesController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ChangePassword;
use App\Http\Controllers\Api\MailController;
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
//Rutas de autenticación
Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);
Route::post("/logout", [AuthController::class, "logout"]);
Route::get('/user/partial_info', [AuthController::class, 'partial_info']);
Route::get('/email/verify',[MailController::class,'verifiedMail']);
Route::get('/email/user/resend',[MailController::class,'resendEmail']);
Route::get('/user/total_info', [AuthController::class, 'total_info']);
Route::post('/recoveryPwd/evalueteToken', [ChangePassword::class, 'evaluateTokenAccess']);
Route::post('/user/reset_password/send_email', [ChangePassword::class, 'sendEmail']);
Route::post('/user/reset_password/reset_password', [ChangePassword::class, 'resetPassword']);
Route::post('/user/reset_password/verificate_password', [ChangePassword::class, 'verificatePassword']);
Route::post('/user/reset_password/changePassword', [ChangePassword::class, 'changePassword']);

//Rutas de promotores
Route::get('/promotores/getNombres', [PromotoresController::class, 'getNombres']);
Route::post('/promotores/registro', [PromotoresController::class, 'register']);
Route::post('/promotores/evalueteToken', [PromotoresController::class, 'evaluateTokenAccess']);
//Rutas de comites
Route::get('/comites/getTotalInfo', [ComitesController::class, 'getTotalInfo']);

//administrador
Route::get("/verify_admin", [AdminController::class, "verify_admin"]);
Route::get("/admin/total_users_info", [AdminController::class, "total_users_info"]);
Route::post('/admin/total_info_admin', [AdminController::class, 'total_info_admin']);
Route::post('/admin/registro/pause', [AdminController::class, 'pause_register']);
Route::post('/admin/registro/play', [AdminController::class, 'play_register']);
Route::post('/admin/registro/edit', [AdminController::class, 'edit_user_info']);
Route::post('/admin/registro/rrmail', [AdminController::class, 'resend_mail_confirmation']); 
Route::get('/admin/sidebar/talleres', [AdminController::class, 'getDataSideBar']);    
Route::get('/admin/getTotalInfo', [PromotoresController::class, 'getTotalInfo']);
Route::get('/admin/generateLink', [PromotoresController::class, 'generateUrl']);
Route::get('/admin/getTokens', [PromotoresController::class, 'getTokens']);
Route::post('/admin/getInfoPromotor',[PromotoresController::class, 'getPromotor']);
Route::post('/admin/getTokensRegistro',[PromotoresController::class, 'getTokensRegistro']);

//Rutas de talleres
Route::get('/talleres/getInfoTalleres', [TalleresController::class, 'getInfoTalleres']);

//user talleres
Route::get('/talleres/infoDataUser', [TalleresController::class, 'getIds']);
Route::get('talleres/registro_taller',[TalleresController::class, 'registro_taller']);
Route::get('talleres/desinscripcion_taller',[TalleresController::class, 'desinscripcion_taller']);

//Rutas para programa
Route::get('/programa/getTotalInfo',[ProgramaController::class, 'getPonencias']);

//Rutas Usuario Actividades
Route::get('/actividades/infoRegistros',[ActividadesController::class, 'getActiveSections']);
Route::post('/actividades/registro_resumenes', [ActividadesController::class, 'registerResumenes']);
Route::get('/actividades/infoResumenes',[ActividadesController::class,'getInfoResumen']);
Route::post('/actividades/registro_fotografia',[ActividadesController::class,'registerFotografia']);
Route::get('/actividades/infoFotografia', [ActividadesController::class,'getInfoFotografia']);
