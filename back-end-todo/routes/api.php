<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TodosController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/get-category', [TodosController::class, 'categories']);
    Route::post('/post-category', [TodosController::class, 'postCategory']);
    Route::post('/post-todo', [TodosController::class, 'postTodo']);
    Route::get('/todos', [TodosController::class, 'toDos']);
    Route::put('/todos-update/{id}', [TodosController::class, 'updateTodo']);
    Route::delete('/todos-delete/{id}', [TodosController::class, 'deleteTodo']);
    Route::post('/logout', [AuthController::class, 'logout']);
});