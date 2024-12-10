<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\PaymentController;

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




Route::apiResources([
    'merchants' => MerchantController::class,
    'payment-methods' => PaymentMethodController::class,
]);

Route::get('payments/merchant/{id}', [PaymentController::class, 'showByMerchantId'])->middleware('auth.merchant');
Route::get('payments/merchant/{merchantId}/payment/{paymentId}', [PaymentController::class, 'showByMerchantIdAndPaymentId'])->middleware('auth.merchant');

Route::post('payments/process', [PaymentController::class, 'processPayment']);
Route::post('auth/login', [MerchantController::class, 'login']);


