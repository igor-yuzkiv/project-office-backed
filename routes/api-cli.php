<?php

use App\Http\CliApi\Controllers\TestController;

Route::get('test', [TestController::class, 'index']);
