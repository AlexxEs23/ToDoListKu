<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;

Route::get('/', [TodoController::class, 'index'])->name('home');
Route::get('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login'); 
Route::post('/login-post', [App\Http\Controllers\AuthController::class, 'loginPost'])->name('loginPost');
Route::get('/register', [App\Http\Controllers\AuthController::class, 'register'])->name('register');
Route::post('/register-post', [App\Http\Controllers\AuthController::class, 'registerPost'])->name('registerPost');

Route::post('/todo/add', [TodoController::class, 'addTodo'])->name('todo.add');
Route::put('/todo/update/{id}', [TodoController::class, 'updateTodo'])->name('todo.update');
Route::delete('/todo/delete/{id}', [TodoController::class, 'deleteTodo'])->name('todo.delete');
Route::get('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::post('/todo/complete/{id}', [TodoController::class, 'completeTodo'])->name('todo.complete');
