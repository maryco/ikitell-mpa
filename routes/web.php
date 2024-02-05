<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect(route('home'));
    }

    return view('welcome');
});

// Static route for Widget SPA
Route::prefix('widget')->group(function () {
    Route::get('/{any}', function () {
        return File::get(public_path() . '/widget/index.html');
    });
});

Auth::routes(['verify' => true]);

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/about', 'DocsController@about')->name('about');
Route::get('/terms', 'DocsController@terms')->name('terms');
Route::post('/device/try/report', 'DeviceController@mockReport')->name('try.report');

// TODO:
Route::get('/contact', 'ContactController@showForm')->name('contact');
//Route::post('/contact', 'ContactController@send')->name('contact');

/*
 * Device routes.
 * NOTE: The 'report' has 'throttle' middleware.
 */
Route::group(['prefix' => 'device', 'as' => 'device.', 'middleware' => ['auth']], function () {
    Route::get('/list', 'DeviceController@getList')->name('list');
    Route::get('/create', 'DeviceController@showForm')->name('create');
    Route::post('/create', 'DeviceController@store')->name('create');
    Route::get('/{id}/edit', 'DeviceController@showForm')->name('edit');
    Route::post('/{id}/edit', 'DeviceController@store')->name('edit');
    Route::post('/{id}/delete', 'DeviceController@delete')->name('delete');
    Route::post('/{id}/report', 'DeviceController@report')->name('report');
});

/*
 * Notice routes.
 */
Route::group(['prefix' => 'notice', 'as' => 'notice.', 'middleware' => ['auth']], function () {
    /**
     * Manage Address
     */
    Route::group(['prefix' => 'address', 'as' => 'address.', 'middleware' => ['verified']], function () {
        Route::get('list', 'NotificationDestinationController@getList')->name('list');
        Route::get('create', 'NotificationDestinationController@showForm')->name('create');
        Route::post('create', 'NotificationDestinationController@store')->name('create');
        Route::get('{id}/edit', 'NotificationDestinationController@showForm')->name('edit');
        Route::post('{id}/edit', 'NotificationDestinationController@store')->name('edit');
        Route::post('{id}/delete', 'NotificationDestinationController@delete')->name('delete');
        Route::post('{id}/verify/send', 'NotificationDestinationController@sendVerification')->name('verify.send');
        Route::post('{id}/verify/preview', 'NotificationDestinationController@previewVerify')->name('verify.preview');
    });

    /**
     * Manage Message
     */
    Route::group(['prefix' => 'message', 'as' => 'message.'], function () {
        Route::post('/preview', 'NoticeMessageController@preview')->name('preview');
    });

    /**
     * Notification Log
     */
    Route::group(['prefix' => 'history', 'as' => 'history.'], function () {
        Route::get('alert/search', 'NoticeLogController@searchAlertLog')->name('alert.search');
        Route::get('alert/{id}/show', 'NoticeLogController@showDetail')->name('alert.detail');
    });
});

/**
 * Verify contacts email address.
 * NOTE: Middleware is set in the Controller.
 */
Route::get('notice/address/{id}/verify', 'NotificationDestinationController@verify')
    ->name('notice.address.verify');

/*
 * Alerting Rule routes.
 */
Route::group(['prefix' => 'rule', 'as' => 'rule.', 'middleware' => ['auth']], function () {
    Route::get('/list', 'RuleController@getList')->name('list');
    Route::get('/create', 'RuleController@showForm')->name('create');
    Route::post('/create', 'RuleController@store')->name('create');
    Route::get('/{id}/edit', 'RuleController@showForm')->name('edit');
    Route::post('/{id}/edit', 'RuleController@store')->name('edit');
    Route::post('/{id}/delete', 'RuleController@delete')->name('delete');
});

/*
 * User Profile routes.
 */
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'middleware' => ['auth']], function () {
    Route::get('/edit', 'ProfileController@showForm')->name('edit');
    Route::post('/edit', 'ProfileController@update')->name('edit');
});

/*
 * Account routes.
 */
Route::group(['prefix' => 'account', 'as' => 'account.', 'middleware' => ['auth']], function () {
    //Route::get('/plan/edit', 'AccountController@showPlanForm')->name('plan');
    //Route::post('/plan/edit', 'AccountController@storePlan')->name('plan');
    Route::post('/password/email', 'AccountController@sendResetLinkEmail')->name('password.email');
    Route::post('/delete', 'AccountController@delete')->name('delete');
});

/*
 * Lab Routes.
 * NOTE: These routes register only local env.
 */
Route::group(['prefix' => 'lab', 'as' => 'lab.'], function () {
    if ('local' !== \Illuminate\Support\Facades\App::environment()) {
        //Log::error("This route only for the local env!");
        return;
    }

    /*
     * Develop for frontend etc
     */
    Route::get('/', function () {
        return view('lab');
    })->name('home');

    Route::get('form', function () {
        $deviceForm = new \App\Http\Requests\DeviceStoreRequest();
        return view('lab.form', compact('deviceForm'));
    })->name('form');

    Route::get('/error', function () {

        $e = new \Symfony\Component\HttpKernel\Exception\HttpException(404, 'メッセージ');

        return view('panels.error', [
            'errors' => new ViewErrorBag(),
            'exception' => $e,
        ]);
    })->name('error');

    Route::get('/panel', function () {
        return view('panels.message', [
            'pageTitle' => __('label.page_title.verify_email'),
            'messages' => [__('message.support.thanks_verified')],
            'linkItems' => [['text' => __('label.link.about'), 'href' => route('about')]]
        ]);
    })->name('panel');

    Route::get('/device', 'LabController@device')->name('device');
    Route::get('/device/mock', 'LabController@mock')->name('mock');
    Route::post('/device/{id}/force/alert', 'LabController@issueAlert')->name('alert');

    Route::post('/mail/preview', 'LabController@mailPreview')->name('mail.preview');

    Route::post('sandbag', function () {

        Log::debug('Requested data = ', ['' => request()->all()]);
        Log::debug('Requested data (dummyText) = ', ['' => request()->get('dummyText')]);

        return response()->json(
            [
                'message' => 'Receive Data!',
                'error' => [],
            ]
        );
    })->name('sandbag');

    // TODO:
    //Route::post('/device/{id}/force/report', 'LabController@deviceReport')->name('report');
});
