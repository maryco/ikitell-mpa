<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Entities\User;
use App\Models\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepo;

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
    protected function validator(array $data)
    {
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
     * @return \App\Models\Entities\User
     */
    protected function create(array $data)
    {
        $user = $this->userRepo->createUserDataSet($data);

        if (!$user) {
            Log::warning('Failed to register new account. [%]', ['' => $data]);
            abort(500);
        }

        return $user;
    }

    /**
     * @see \Illuminate\Foundation\Auth\RegistersUsers::registered
     */
    protected function registered(Request $request, $user)
    {
        if ($request->ajax()) {
            return response_json_redirection(
                'Registered, please redirect to the home',
                url($this->redirectTo)
            );
        }

        return redirect($this->redirectTo)
            ->with('registered', true);
    }
}
