<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentMethodController;
use App\Services\Track123Service;


use App\Models\Product;
use App\Models\Category;
use App\Models\User;

Route::middleware('doctor')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    });
    Route::post('/login', [UserController::class, 'login'])->name('login');

    Route::get('/work', function () {
        return view('work');
    })->name('work');
//Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    Route::get('/register', function () {
        return view('auth.register');
    });


    Route::get('/contact-complete', [DashboardController::class, 'contactComplete'])->name('contact-complete');

    Route::get('/reset-password/{token}', [UserController::class, 'resetToken'])->name('resetToken');
    Route::post('/send-reset-email', [UserController::class, 'sendResetEmail'])->name('sendResetEmail');
    Route::post('/reset-password', [UserController::class, 'resetPassword'])->name('resetPassword');

    Route::get('/terms-of-service', function () {
        return view('quick_links.terms-of-service');
    });

    Route::get('/terms-of-sell', function () {
        return view('quick_links.terms-of-sell');
    });

    Route::get('/modalita-costi-spedizione', function () {
        return view('quick_links.modalita-costi-spedizione');
    });

    Route::get('/pagamenti-accettati', function () {
        return view('quick_links.pagamenti-accettati');
    });

    Route::get('/privacy-policy', function () {
        return view('quick_links.privacy-policy');
    });

    Route::get('/returns-policy', function () {
        return view('quick_links.returns-policy');
    });

    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    });

    Route::get('/shop-single/{id}', [ProductController::class, 'shop_single'])->name('shop_single');

    Route::get('/shop-grid/{token}', [ProductController::class, 'shop_grid_category'])->name('shop_grid_category');

    Route::get('/shop-search', [ProductController::class, 'shop_search'])->name('shop_search');

    Route::get('/shop-search/{type}', [ProductController::class, 'shop_search'])->name('shop_search_type');

    Route::middleware('auth')->group(function () {

        Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

        Route::get('/shop-cart', [CartController::class, 'shop_cart'])->name('cart.shop_cart');
        Route::get('/shop-checkout', [CartController::class, 'shop_checkout'])->name('cart.shop_checkout');
        Route::get('/shop-checkout-complete', [CartController::class, 'shop_checkout_complete'])->name('cart.shop_checkout_complete');

        Route::post('/cart/update-quantity/{cartItem}', [CartController::class, 'updateQuantity'])->name('cart.updateQuantity');

        Route::get('/order-list', [OrderController::class, 'index'])->name('index');
        Route::get('/order-detail/{id}', [OrderController::class, 'orderDetail'])->name('orderDetail');

        Route::group(['prefix'=>'settings','as'=>'settings'], function(){

            Route::get('/billing-address', [UserController::class, 'billing_address'])->name('billing_address');
            Route::get('/shipping-address', [UserController::class, 'shipping_address'])->name('shipping_address');

            Route::get('/user-profile', function () {
                return view('auth.user-profile');
            });

            Route::post('/edit-user-profile', [UserController::class, 'edit_user_profile'])->name('edit_user_profile');
            Route::post('/update-password', [UserController::class, 'updatePassword'])->name('updatePassword');

            
            Route::get('/order-delete/{id}', [OrderController::class, 'orderDelete'])->name('orderDelete');

            Route::get('/edit-address/billing', [UserController::class, 'edit_billing_address'])->name('edit_billing_address');
            Route::get('/edit-address/shipping', [UserController::class, 'edit_shipping_address'])->name('edit_shipping_address');

            Route::post('/edit-address/billing', [UserController::class, 'edit_billing_address_private'])->name('edit_billing_address_private');
            Route::post('/edit-address/shipping', [UserController::class, 'edit_shipping_address_post'])->name('edit_shipping_address_post');

            Route::get('/payment-method', [UserController::class, 'payment_method'])->name('payment_method');

            Route::get('/add-payment', function () {
                return view('auth.add-payment');
            });
        });

        Route::post('/payment/customer/create', [PaymentController::class, 'createCustomer'])->name('payment.customer.create');
        Route::post('/payment-method/store', [PaymentMethodController::class, 'store'])->name('payment-method.store');
        Route::delete('/payment-method/delete/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('payment-method.destroy');
        Route::get('/logout', [UserController::class, 'logout'])->name('logout');

        Route::post('/payment/order', [CheckoutController::class, 'checkout'])->name('checkout');

        Route::get('/refund-request/{id}',[OrderController::class, 'refundRequest'])->name('refund-request');
        Route::post('/refund-request/{id}',[OrderController::class, 'refundRequestPost'])->name('refund-request-post');

        Route::get('/paypal/pay/{order}', [CheckoutController::class,'createPayment'])->name('paypal.pay');

        Route::get('/paypal/success', [CheckoutController::class,'success'])->name('paypal.success');

        Route::get('/paypal/cancel', function(){
            return redirect()->route('cart.shop_checkout')->with('error','Pagamento annullato');
        })->name('paypal.cancel');

        Route::post('/delete-account', [UserController::class, 'delete_account'])->name('delete-account');
    });


    Route::get('/cities/search', [CityController::class, 'search'])->name('cities.search');
    Route::post('/register', [UserController::class, 'register'])->name('register');

    Route::get('/track', [TrackingController::class, 'track']);
    Route::get('/contact', [DashboardController::class, 'contact'])->name('contact');
    Route::post('/sendRequestContact', [DashboardController::class, 'sendRequestContact'])->name('sendRequestContact');
    Route::get('/brand', [DashboardController::class, 'brand'])->name('brand');
    });
//});