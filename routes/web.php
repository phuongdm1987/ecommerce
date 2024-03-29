<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['verify' => true]);
Route::get('logout', 'Auth\LoginController@logout');

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/home', 'HomeController@index')
        ->name('home');

    Route::get('/categories/{category}', 'CategoryController@index')
        ->name('category.index');

    Route::resource('product-users', 'ProductUserController');
    Route::resource('products', 'ProductController');
});

Route::get('/set-language/{locale}', 'LanguageController@update')
    ->name('setLanguage');
