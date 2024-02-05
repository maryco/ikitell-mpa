<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Entities\User;
use App\Models\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected string $redirectTo = '/home';

    /**
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $userRepo;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->middleware('guest');

        $this->userRepo = $userRepo;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        // TODO: Update password policy.
        return Validator::make($data, [
            // NOTE: Not use users.name but exist a column.
            //'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:200', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data): User
    {
        $user = $this->userRepo->createUserDataSet($data);

        if (!$user) {
            Log::warning('Failed to register new account.', ['request' => $data]);
            abort(500);
        }

        return $user;
    }

    /**
     * @param Request $request
     * @param $user
     * @return RedirectResponse|Application|Redirector|JsonResponse
     * @see RegistersUsers::registered
     */
    protected function registered(Request $request, $user): RedirectResponse|Application|Redirector|JsonResponse
    {
        if ($request->ajax()) {
            return response_json_redirection(
                'Registered, please redirect to the home',
                url($this->redirectTo)
            );
        }

        return redirect($this->redirectTo)->with('registered', true);
    }
}
