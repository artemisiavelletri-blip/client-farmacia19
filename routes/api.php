<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\CategoryProductsController;
use App\Http\Controllers\Api\ProductSearchController;
use App\Http\Controllers\Api\ProductGroupController;
use App\Http\Controllers\Api\RelatedProductsController;
use App\Http\Controllers\Api\ProductDetailController;

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

    });

});