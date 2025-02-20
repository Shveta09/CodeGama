<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;


Route::post('register', [UserController::class, 'register']);

Route::post('login', [UserController::class, 'login']);
Route::middleware('auth:api')->get('wallet/checkbalance', [WalletController::class, 'checkBalance']);

Route::middleware('auth:api')->get('user', [UserController::class, 'profile']);
Route::middleware('auth:api')->post('wallet/add', [WalletController::class, 'addFunds']);
Route::middleware('auth:api')->post('wallet/setlimit', [WalletController::class, 'setLimit']);
Route::middleware('auth:api')->post('wallet/withdraw', [WalletController::class, 'withdrawFunds']);
Route::middleware('auth:api')->post('wallet/transfer', [WalletController::class, 'transferFunds']);
Route::middleware('auth:api')->get('/wallet/transactions', [WalletController::class, 'transactionHistory']); // Get transaction history

