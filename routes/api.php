<?php

use Illuminate\Http\Request;

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

/*
 * Lab Routes.
 * NOTE: These routes register only local env.
 */
Route::group(['prefix' => 'lab', 'as' => 'lab.', 'middleware' => 'api'], function () {

    if ('local' !== \Illuminate\Support\Facades\App::environment()) {
        //Log::error("This API only for the local env!");
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
                    'enableReset' =>  false,
                    'remainingTime' => 24,
                    'limitTime' => 24,
                    'resetLimitAt' => now()->addHour(48)->toDateTimeString(),
                 ],
            ]
        );
    });
});
