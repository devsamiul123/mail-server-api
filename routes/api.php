<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MailController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/send-mail', [MailController::class, 'sendMail']);

Route::get('/inbox', [MailController::class, 'getInbox']);

Route::get('/unread-mails', [MailController::class, 'getUnreadEmails']);

Route::post('/search-inbox', [MailController::class, 'searchInbox']);

Route::get('/spam-mails', [MailController::class, 'getSpamMails']);
