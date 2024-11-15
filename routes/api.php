<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::get('categories', [CategoryController::class, 'index']); 
Route::post('category/save', [CategoryController::class, 'save']); 
Route::put('category/update', [CategoryController::class, 'update']); 
Route::get('category/view/{id}', [CategoryController::class, 'show']); 
Route::delete('category/delete/{id}', [CategoryController::class, 'destroy']); 

Route::get('products', [ProductController::class, 'index']); 
Route::post('product/save', [ProductController::class, 'save']); 
Route::get('product/view/{id}', [ProductController::class, 'show']); 
Route::post('product/delete', [ProductController::class, 'destroy']); 

Route::get('customers', [CustomerController::class, 'index']); 
Route::post('customer/save', [CustomerController::class, 'save']); 
Route::get('customer/view/{id}', [CustomerController::class, 'show']); 
Route::post('customer/delete', [CustomerController::class, 'destroy']);

Route::post('cart/add', [CartController::class, 'addToCart']);
Route::get('cart/view', [CartController::class, 'viewCart']);
Route::post('cart/remove', [CartController::class, 'removeFromCart']);

Route::post('invoice/generate', [InvoiceController::class, 'generateInvoice']);