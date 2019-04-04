<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Show edit the profile form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showForm()
    {
        $profile = $this->userRepo->findById(Auth::id());

        if (!$profile) {
            abort(404);
        }

        return view('account.form_profile', compact('profile'));
    }

    /**
     *
     * @param ProfileUpdateRequest $request
     * @throws \Throwable
     */
    public function update(ProfileUpdateRequest $request)
    {
        $profile = $this->userRepo->findById(Auth::id());

        if (!$profile) {
            abort(404);
        }

        if (!$this->userRepo->updateProfile($request->onlyForStore(), Auth::id())) {
            abort(500);
        }

        return redirect(route('profile.edit'))
            ->with('edited', true);
    }
}
