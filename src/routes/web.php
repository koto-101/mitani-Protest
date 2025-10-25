<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EvaluationController;
use Illuminate\Http\Request;


Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/mypage/profile');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '確認メールを再送信しました！');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


Route::get('/', [ItemController::class, 'index']);
Route::get('/register', [UserController::class, 'show']);
Route::post('/register', [UserController::class, 'register']);
Route::get('/login', [UserController::class, 'showLoginForm']);
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show');

Route::get('/purchase/success', [StripeController::class, 'success'])->name('purchase.stripe.success');
Route::get('/purchase/cancel', [StripeController::class, 'cancel'])->name('purchase.stripe.cancel');
Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);


Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/item/{item}/like-toggle', [ItemController::class, 'toggleLike']);
    Route::post('/item/{item}/comment', [ItemController::class, 'comment']);
    Route::get('/sell', [ItemController::class, 'exhibition']);
    Route::post('/sell', [ItemController::class, 'store']);

    Route::get('/mypage', [ProfileController::class, 'show'])->name('mypage.show');
    Route::get('/mypage/profile', [ProfileController::class, 'edit']);       // 編集画面表示
    Route::patch('/mypage/profile', [ProfileController::class, 'update']);  // 更新処理

    Route::get('/purchase/{item_id}', [PurchaseController::class, 'checkout'])->name('purchase.checkout');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'processPurchase'])->name('purchase.process');

    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.edit_address');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.update_address');

    Route::post('/purchase/{item_id}/stripe-checkout', [StripeController::class, 'createCheckoutSession'])->name('purchase.stripe.checkout');

    Route::get('/mypage/chat/{chatRoom}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/mypage/chat/{chatRoom}/message', [ChatController::class, 'store'])->name('chat.message.store');
    Route::patch('/mypage/chat/message/{chatMessage}', [ChatController::class, 'update'])->name('chat.message.update');
    Route::delete('/mypage/chat/message/{chatMessage}', [ChatController::class, 'destroy'])->name('chat.message.destroy');

    Route::post('/mypage/chat/{chatRoom}/evaluate', [EvaluationController::class, 'store'])->name('evaluation.store');
    Route::post('/mypage/chat/{chatRoom}/complete', [TransactionController::class, 'complete'])->name('transaction.complete');
});
