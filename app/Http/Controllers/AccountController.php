<?php

namespace App\Http\Controllers;

use App\Exceptions\IkitellRuntimeException;
use App\Models\Repositories\UserRepositoryInterface;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Mockery\Exception;

class AccountController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * @param Request $request
     * @see \Illuminate\Foundation\Auth\SendsPasswordResetEmails::validateEmail
     */
    protected function validateEmail(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                Rule::exists('users')->where(function ($query) {
                    // Validate the input is current user's one.
                    $query->where('id', Auth::id());
                })
            ]
        ]);
    }

    /**
     * @param Request $request
     * @param $response
     * @see\Illuminate\Foundation\Auth\SendsPasswordResetEmails::sendResetLinkResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        Auth::logout();

        return redirect('/')->with('status', trans($response));
    }

    /**
     * Delete account.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete()
    {
        try {
            if (!$this->userRepo->delete(Auth::id())) {
                abort(500, __('message.error.whoops'));
            }
        } catch (IkitellRuntimeException $e) {
            abort(500, __('message.error.whoops'));
        } catch (Exception $e) {
            throw $e;
        }

        Auth::logout();

        return redirect('/')->with('deregistered', true);
    }
}
