<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

/*
 * TODO: remove
 * Lab Routes.
 * NOTE: These routes register only local env.
 */
Route::group(['prefix' => 'lab', 'as' => 'lab.', 'middleware' => 'api'], function () {

    if ('local' !== App::environment()) {
        return;
    }

    Route::post('gauge/reset', function () {
        return response()->json(
            [
                'message' => 'This is a dummy result.',
                'deviceInfo' => [
                    'icon' => 'apple',
                    'name' => 'あいふぉん',
                    'lastResetAt' => now()->toDateTimeString(),
                    'isAlert' => false,
                    'enableReset' => false,
                    'remainingTime' => 24,
                    'limitTime' => 24,
                    'resetLimitAt' => now()->addHour(48)->toDateTimeString(),
                ],
            ]
        );
    });
});
