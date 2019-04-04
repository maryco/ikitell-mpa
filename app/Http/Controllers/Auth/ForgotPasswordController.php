<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Where to redirect users after send mail.
     *
     * @var string
     */
    protected $redirectToSent = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Override. For the change redirect location.
     *
     * @see \Illuminate\Foundation\Auth\SendsPasswordResetEmails::sendResetLinkResponse
     */
    public function sendResetLinkResponse(Request $request, $response)
    {
        return redirect($this->redirectToSent)->with('status', trans($response));
    }
}
