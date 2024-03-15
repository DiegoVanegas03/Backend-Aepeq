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
use App\Http\Controllers\Api\FacturasController;
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
Route::get('/user/constancia', [AuthController::class,'get_constancia']);
Route::post('/user/register_evaluacion', [AuthController::class,'register_evaluacion']);
Route::post('/recoveryPwd/evalueteToken', [ChangePassword::class, 'evaluateTokenAccess']);
Route::post('/user/reset_password/send_email', [ChangePassword::class, 'sendEmail']);
Route::post('/user/reset_password/reset_password', [ChangePassword::class, 'resetPassword']);
Route::post('/user/reset_password/verificate_password', [ChangePassword::class, 'verificatePassword']);
Route::post('/user/reset_password/changePassword', [ChangePassword::class, 'changePassword']);

Route::get('/senso/count_users_asosiaciones',[AdminController::class,'count_users_asosiaciones']);

//Rutas de promotores
Route::get('/promotores/getNombres', [PromotoresController::class, 'getNombres']);
Route::post('/promotores/registro', [PromotoresController::class, 'register']);
Route::post('/promotores/evalueteToken', [PromotoresController::class, 'evaluateTokenAccess']);
Route::post('/promotores/getInscritos', [PromotoresController::class, 'getInscritos']);

//Rutas de comites
Route::get('/comites/getTotalInfo', [ComitesController::class, 'getTotalInfo']);
Route::post('/comite/updatePrograma', [ComitesController::class, 'updateComites']);

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
Route::get('/admin/asistencia/infoAsistenciaGeneral',[AdminController::class, 'infoAsistenciaGeneral']);
Route::post('/admin/asistencia/tomarAsistencia',[AdminController::class, 'tomarAsistencia']);
Route::post('/admin/asistencia/registerMochila',[AdminController::class, 'registerMochila']);
Route::get('/admin/asistencia/infoAsistenciaTaller',[AdminController::class, 'infoAsistenciaTaller']);
Route::post('/admin/asistencia/tomarAsistenciaTaller',[AdminController::class, 'tomarAsistenciaTaller']);
Route::get('/admin/get_info_constancias_generales',[AdminController::class, 'get_info_constancias_generales']);
Route::get('/admin/generar_constancias',[AdminController::class, 'generar_constancias']);
Route::post('/admin/generar_constancias_taller',[AdminController::class, 'generar_constancias_taller']);
Route::post('/admin/get_info_constancias_talleres',[AdminController::class, 'get_info_constancias_talleres']);
Route::get('/admin/mergeConstanciasTalleres',[AdminController::class, 'mergeConstanciasTalleres']);
Route::get('/admin/recordatorio_congreso',[MailController::class, 'mailRecordatorio']);
Route::post('/admin/GetInfoConstanciaBajoAgua',[AdminController::class, 'GetInfoConstanciaBajoAgua']);
Route::post('/admin/generar_constancias_bajo_agua',[AdminController::class, 'ConstanciasBajoElAgua']);
Route::post('/admin/delete_constancias_bajo_agua',[AdminController::class, 'deleteConBajoAgua']);



//Rutas de talleres
Route::get('/talleres/getInfoTalleres', [TalleresController::class, 'getInfoTalleres']);
Route::post('/talleres/getInfoTaller', [TalleresController::class, 'getInfoTaller']);
Route::get('/talleres/descargar_listas', [TalleresController::class, 'descargar_listas']);

//user talleres
Route::get('/talleres/infoDataUser', [TalleresController::class, 'getIds']);
Route::get('/talleres/registro_taller',[TalleresController::class, 'registro_taller']);
Route::get('/talleres/desinscripcion_taller',[TalleresController::class, 'desinscripcion_taller']);
Route::post('/talleres/updateInfoTaller',[TalleresController::class, 'updateInfoTaller']);
Route::post('/talleres/addTaller',[TalleresController::class, 'addTaller']);
Route::post('/talleres/deleteTaller',[TalleresController::class, 'deleteTaller']);
Route::post('/talleres/changeAula',[TalleresController::class, 'changeAula']);

//Rutas para programa
Route::get('/programa/getTotalInfo',[ProgramaController::class, 'getPonencias']);
Route::post('/programa/updatePrograma',[ProgramaController::class, 'updatePonencias']);

//Rutas Usuario Actividades
Route::get('/actividades/infoRegistros',[ActividadesController::class, 'getActiveSections']);
Route::post('/actividades/registro_resumenes', [ActividadesController::class, 'registerResumenes']);
Route::get('/actividades/infoResumenes',[ActividadesController::class,'getInfoResumen']);
Route::post('/actividades/registro_fotografia',[ActividadesController::class,'registerFotografia']);
Route::get('/actividades/infoFotografia', [ActividadesController::class,'getInfoFotografia']);
Route::get('/actividades/getEstados', [ActividadesController::class,'getEstados']);
Route::post('/actividades/registro_traje_tipico',[ActividadesController::class,'registerTrajeTipico']);
Route::post('/actividades/traje_tipico/aceptarRegistro',[ActividadesController::class,'aceptarPeticion']);
Route::get('/actividades/infoTrajeTipico', [ActividadesController::class,'getInfoTrajeTipico']);
Route::get('/actividades/traje_tipico/cancelarRegistro',[ActividadesController::class,'cancelarTrajeTipico']);
Route::get('/actividades/traje_tipico/reenviarInvitacion',[ActividadesController::class,'ReenviarTrajeTipico']);
Route::get('/actividades/getResumenes',[ActividadesController::class,'getResumenes']);
Route::post('/actividades/aceptarResumen',[ActividadesController::class,'aceptacionResumen']);
Route::post('/actividades/uploadExtensioFile',[ActividadesController::class,'uploadExtensioFile']);
Route::post('/actividades/pedir_correciones',[ActividadesController::class,'pedir_correciones']);
Route::post('/actividades/solicitar_extension',[ActividadesController::class,'solicitar_extension']);
Route::get('/actividades/fotografia/getFotografias', [ActividadesController::class, 'getFotografias']);
Route::get('/actividades/fotografia/descargarExcel', [ActividadesController::class, 'descargarExcel']);
Route::get('/actividades/traje_tipico/getTrajeTipico', [ActividadesController::class, 'getTrajeTipico']);
Route::get('/actividades/traje_tipico/descargarExcelTrajeTipico', [ActividadesController::class, 'descargarExcelTrajeTipico']);

//Rutas de facturas
Route::get('/facturas/getAll', [FacturasController::class, 'getAll']);
Route::post('/facturas/realizar', [FacturasController::class, 'realizarFactura']);

