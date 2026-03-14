<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MobileApp\MasterController;
use App\Http\Controllers\API\MobileApp\UserController;
use App\Http\Controllers\API\MobileApp\PostController;
use App\Http\Controllers\API\MobileApp\PreLoginControllerMobile;
use App\Http\Controllers\API\Auth\PreLoginController;
use App\Http\Controllers\API\MobileApp\SearchHistoryController;
use App\Http\Controllers\API\MobileApp\AuthController;
use App\Http\Controllers\API\MobileApp\SubscriptionPlanController;
use App\Http\Controllers\API\MobileApp\PaymentController;
use App\Http\Controllers\API\MobileApp\AgentController;
use App\Http\Controllers\API\MobileApp\NotificationController;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// For SR community Mobile App
Route::post('/google-login', [AuthController::class, 'googleLogin']);
Route::post('/apple-login', [AuthController::class, 'appleLogin']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::post('get-user',[PreLoginController::class, 'getUserbyId']);
Route::post('getcountry',[PreLoginController::class, 'getcountry']);
Route::post('getstate',[PreLoginController::class, 'getstate']);
Route::post('getcity',[PreLoginController::class, 'getcity']);
Route::post('getAllCurrency',[PreLoginController::class, 'getAllCurrency']);
Route::post('getcountryname',[PreLoginController::class, 'getcountryname']);
// Route::get('dump-table', [ShiftController::class, 'table']);
// Route::post('exportDatabase',[MyReportController::class, 'exportDatabase']);

Route::post('admin_login', [PreLoginControllerMobile::class, 'mobile_admin_login']);
Route::post('mobile_register', [PreLoginControllerMobile::class, 'mobile_register']);
Route::post('mobile_login', [PreLoginControllerMobile::class, 'mobile_login']);
Route::post('mobile_forgot_password',[PreLoginControllerMobile::class, 'mobile_forgotPassword']);
Route::post('mobile_reset_password',[PreLoginControllerMobile::class, 'mobile_resetPassword']);

Route::group(['middleware' => ['auth:sanctum']], function () {

    // Notificatio Api
    Route::post('notification/list', [NotificationController::class, 'getNotification']);
    Route::post('notification/delete', [NotificationController::class, 'deleteNotification']);
    Route::post('notification/delete/all', [NotificationController::class, 'deleteAllNotification']);

    // Subscription Plan API
    Route::post('subscription/plan/create', [SubscriptionPlanController::class, 'createSubscriptionPlan']);
    Route::post('subscription/plan/list', [SubscriptionPlanController::class, 'getSubscriptionPlans']);
    Route::post('subscription/plan/details', [SubscriptionPlanController::class, 'getSubscriptionPlanById']);

    // Payment API
    Route::post('payment/create', [PaymentController::class, 'verifyAndSavePayment']);
    Route::post('payment/user', [PaymentController::class, 'listUserPayments']);    //get users payment
    Route::post('subscription/user', [PaymentController::class, 'listUserSubscriptions']);    //get exists subscription
    Route::post('subscription/user/dashboard', [PaymentController::class, 'listUserSubscriptionsDashboard']);    //get exists subscription

    // Agent API
    Route::post('agent/create/update', [AgentController::class, 'createAgent']);
    Route::post('agent/list', [AgentController::class, 'getAgents']);
    Route::post('agent/details', [AgentController::class, 'getAgentDetails']);


    //mobile login api
    Route::post('mobile_change_password',[PreLoginControllerMobile::class, 'mobile_changePassword']);
    Route::post('mobile_reset_password',[PreLoginControllerMobile::class, 'mobile_resetPassword']);
    Route::post('mobile_user_profile',[PreLoginControllerMobile::class, 'mobile_userProfile']);
    Route::post('mobile_update_profile',[PreLoginControllerMobile::class, 'mobile_updateProfile']);
    Route::post('mobile_logout',[PreLoginControllerMobile::class, 'mobile_logout']);

    // mobile app api
    Route::post('master/create/update',[MasterController::class, 'masterCreateUpdate']);
    Route::post('master/list',[MasterController::class, 'getMaster']);
    Route::post('master/delete',[MasterController::class, 'deleteMaster']);
    

    // mobile app api
    Route::post('master/create/update',[MasterController::class, 'masterCreateUpdate']);
    Route::post('master/list',[MasterController::class, 'getMaster']);
    Route::post('master/delete',[MasterController::class, 'deleteMaster']);
    Route::post('master/statusupdate',[MasterController::class, 'updateStatus']);

    Route::post('master/post_type_list',[MasterController::class, 'post_type_lists']);
    
    // mobile app api
    Route::post('user/create/update',[UserController::class, 'mobileAppUserCreateUpdate']);
    Route::post('user/list',[UserController::class, 'getMobileAppUser']);
    Route::post('user/details',[UserController::class, 'detailsMobileAppUser']);
    Route::post('user/delete',[UserController::class, 'deleteMobileAppUser']);
    Route::post('user/update/status',[UserController::class, 'updateStatus']);
    Route::post('user/follow', [UserController::class, 'follow']);
    Route::post('user/followers/{id}', [UserController::class, 'followers']);
    Route::post('user/following', [UserController::class, 'following']);
    Route::post('user/unfollow', [UserController::class, 'unfollow']);
    Route::post('user/set/fcmToken', [UserController::class, 'updateFcmToken']);

    Route::post('user/web/followers', [UserController::class, 'web_followers']);
    Route::post('user/web/following', [UserController::class, 'web_following']);
       

    // post app api
    Route::post('post/create/update',[PostController::class, 'postCreateUpdate']);
    Route::post('post/list',[PostController::class, 'getPost']);
    Route::post('post/delete',[PostController::class, 'deletePost']);
    Route::post('post/update/status',[PostController::class, 'updateStatus']);
    Route::post('post/details',[PostController::class, 'postDetails']);
    Route::post('post/details/user',[PostController::class, 'postDetailsUser']);
    Route::post('post/filterby',[PostController::class, 'postFilterby']);
    Route::post('post/bookmark/add', [PostController::class, 'addBookmark']);
    Route::post('post/bookmark/remove', [PostController::class, 'removeBookmark']);
    Route::post('post/bookmark/user', [PostController::class, 'fetchBookmarks']);

    // Count all posts in filter
    Route::get('post/all/records', [PostController::class, 'fetchRecordsCount']);


    Route::post('post/reported',[PostController::class, 'post_reportedsCreateUpdate']);
    Route::post('post/reported/list',[PostController::class, 'getPost_reporteds']);
    Route::post('post/overview',[PostController::class, 'postOverView']);

    // Search History Clear
    Route::post('search/history/list',[SearchHistoryController::class, 'listSearchHistory']);
    Route::post('search/history/clear',[SearchHistoryController::class, 'clearSearchHistory']);
});