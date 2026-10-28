<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\CategoryProductsController;
use App\Http\Controllers\Api\ProductSearchController;
use App\Http\Controllers\Api\ProductGroupController;
use App\Http\Controllers\Api\RelatedProductsController;
use App\Http\Controllers\Api\ProductDetailController;
use App\Http\Controllers\Api\Farmacia19CartCouponController;
use App\Http\Controllers\Api\Farmacia19CheckoutApiController;
use App\Http\Controllers\Api\Farmacia19PaypalCheckoutController;

use App\Http\Controllers\Api\Farmacia19AuthController;
use App\Http\Middleware\Farmacia19ApiToken;

Route::prefix('v1')->group(function () {

    // HOME
    Route::get('/home', [HomeController::class, 'index']);

    // CATEGORIE
    Route::get('/categories/{token}/products', [
        CategoryProductsController::class, 'index'
    ]);

    // RICERCA
    Route::get('/products/search', [
        ProductSearchController::class, 'index'
    ]);

    // GRUPPI PRODOTTO
    Route::get('/product-groups/{id}', [
        ProductGroupController::class, 'show'
    ]);

    // PRODOTTI CORRELATI
    Route::get('/products/related', [
        RelatedProductsController::class, 'index'
    ]);

    // DETTAGLIO PRODOTTO
    Route::get('/products/{id}', [
        ProductDetailController::class, 'show'
    ])->whereNumber('id');

    // LOGIN APP
    Route::post('/auth/login', [
        Farmacia19AuthController::class, 'login'
    ])->middleware('throttle:5,1');

    Route::post('/auth/register', [
        \App\Http\Controllers\Api\Farmacia19RegistrationController::class,
        'register'
    ])->middleware('throttle:5,1');

    Route::get('/cities', [
        \App\Http\Controllers\Api\Farmacia19CitiesController::class,
        'index'
    ])->middleware('throttle:30,1');


    Route::get(
        '/app/payment-method/bridge/{ticket}',
        [\App\Http\Controllers\Api\Farmacia19CardBridgeController::class, 'enter']
    )
    ->middleware('web')
    ->name('farmacia19.card.bridge');


    // ROTTE PROTETTE APP
    Route::middleware(Farmacia19ApiToken::class)->group(function () {

        Route::get('/auth/me', [
            Farmacia19AuthController::class, 'me'
        ]);

        Route::post('/auth/logout', [
            Farmacia19AuthController::class, 'logout'
        ]);

        Route::get('/cart', [\App\Http\Controllers\Api\Farmacia19CartController::class, 'index']);
        Route::post('/cart/add', [\App\Http\Controllers\Api\Farmacia19CartController::class, 'add']);
        Route::patch('/cart/items/{id}', [\App\Http\Controllers\Api\Farmacia19CartController::class, 'update']);
        Route::delete('/cart/items/{id}', [\App\Http\Controllers\Api\Farmacia19CartController::class, 'remove']);

        Route::patch('/account/profile', [\App\Http\Controllers\Api\Farmacia19ProfileController::class, 'update']);
        Route::post('/account/password', [\App\Http\Controllers\Api\Farmacia19ProfileController::class, 'password']);
        Route::post('/account/delete', [\App\Http\Controllers\Api\Farmacia19ProfileController::class, 'delete']);

        Route::get('/account/billing-address', [\App\Http\Controllers\Api\Farmacia19BillingController::class, 'show']);
        Route::patch('/account/billing-address', [\App\Http\Controllers\Api\Farmacia19BillingController::class, 'update']);

        Route::get('/account/shipping-addresses', [\App\Http\Controllers\Api\Farmacia19ShippingController::class, 'index']);
        Route::post('/account/shipping-addresses', [\App\Http\Controllers\Api\Farmacia19ShippingController::class, 'store']);
        Route::patch('/account/shipping-addresses/{id}', [\App\Http\Controllers\Api\Farmacia19ShippingController::class, 'update'])->whereNumber('id');
        Route::patch('/account/shipping-addresses/{id}/default', [\App\Http\Controllers\Api\Farmacia19ShippingController::class, 'setDefault'])->whereNumber('id');
        Route::delete('/account/shipping-addresses/{id}', [\App\Http\Controllers\Api\Farmacia19ShippingController::class, 'destroy'])->whereNumber('id');

        Route::get('/account/payment-methods', [\App\Http\Controllers\Api\Farmacia19PaymentMethodsController::class, 'index']);
        Route::post('/account/payment-methods/start', [\App\Http\Controllers\Api\Farmacia19PaymentMethodsController::class, 'start'])->middleware('throttle:10,1');
        Route::delete('/account/payment-methods/{id}', [\App\Http\Controllers\Api\Farmacia19PaymentMethodsController::class, 'destroy'])->whereNumber('id');

        Route::get('/cart/summary', [Farmacia19CartCouponController::class, 'summary']);
        Route::post('/cart/coupon', [Farmacia19CartCouponController::class, 'addCoupon']);
        Route::post('/cart/coupon/remove', [Farmacia19CartCouponController::class, 'removeCoupon']);
        Route::post('/checkout/confirm', [Farmacia19CheckoutApiController::class, 'confirm']);

        Route::get('/account/orders', [\App\Http\Controllers\Api\Farmacia19OrdersApiController::class, 'index']);
        Route::get('/account/orders/{orderNumber}', [\App\Http\Controllers\Api\Farmacia19OrdersApiController::class, 'show']);
        Route::get('/account/orders/{orderNumber}/tracking', [\App\Http\Controllers\Api\Farmacia19OrdersApiController::class, 'tracking']);
        Route::post('/account/orders/{orderNumber}/cancel', [\App\Http\Controllers\Api\Farmacia19OrdersApiController::class, 'cancel']);
        Route::get('/account/return-reasons', [\App\Http\Controllers\Api\Farmacia19OrdersApiController::class, 'reasons']);
        Route::post('/account/orders/{orderNumber}/refund', [\App\Http\Controllers\Api\Farmacia19OrdersApiController::class, 'refund']);

        Route::get('/account/coupons', [\App\Http\Controllers\Api\Farmacia19CouponsApiController::class, 'index']);
        Route::post('/account/coupons/reveal', [\App\Http\Controllers\Api\Farmacia19CouponsApiController::class, 'reveal']);

        Route::get('/account/coupons/{token}/products', [\App\Http\Controllers\Api\Farmacia19CouponProductsController::class, 'index']);

        Route::post(
            '/checkout/paypal/start',
            [Farmacia19PaypalCheckoutController::class, 'start']
        );

        Route::get(
            '/checkout/paypal/status',
            [Farmacia19PaypalCheckoutController::class, 'status']
        );

        Route::get('/checkout/paypal/recover', [Farmacia19PaypalCheckoutController::class, 'recover']);

        Route::post(
            '/checkout/paypal/abandon',
            [Farmacia19PaypalCheckoutController::class, 'abandon']
        )->middleware('throttle:10,1');

    });

});