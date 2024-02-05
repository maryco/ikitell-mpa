<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactStoreRequest;
use App\Models\Entities\Contact;
use App\Models\Repositories\ContactRepositoryInterface;
use App\Notifications\VerifyRequestContactsNotification;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class NotificationDestinationController extends Controller
{
    /**
     * @var ContactRepositoryInterface
     */
    protected ContactRepositoryInterface $contactRepo;

    /**
     * NoticeAddressController constructor.
     * @param ContactRepositoryInterface $contactRepo
     */
    public function __construct(ContactRepositoryInterface $contactRepo)
    {
        $this->middleware(['signed', 'throttle:6,1'])->only('verify');

        $this->contactRepo = $contactRepo;
    }

    /**
     * Get contacts list.
     *
     * @return Factory|View
     */
    public function getList(): Factory|View
    {
        $contacts = $this->contactRepo->getByUserId(Auth::id());

        return view('notice.address.list', compact('contacts'));
    }

    /**
     * Show create/edit form.
     *
     * @param ?int $id
     * @return Factory|View
     */
    public function showForm(int $id = null): Factory|View
    {
        $contact = $this->contactRepo->makeModel();

        if (Route::getCurrentRoute()?->named('notice.address.edit')) {
            $contact = $this->contactRepo->findByUserId(Auth::id(), $id);
            abort_if(
                !$contact,
                Response::HTTP_NOT_FOUND,
                __('message.error.notfound', ['attribute' => __('label.notice_address')])
            );
        }

        // TODO: Implements. (if need)
        //$contactForm = new ContactStoreRequest();

        return view('notice.address.form', compact('contact'));
    }

    /**
     * Store the contacts.
     *
     * @param ContactStoreRequest $request
     * @return RedirectResponse|Redirector
     */
    public function store(ContactStoreRequest $request): Redirector|RedirectResponse
    {
        $appInfoKey = self::ACTION_RESULT_KEY_SAVE;
        $targetId = Route::getCurrentRoute()?->parameter('id');

        if (Route::getCurrentRoute()?->named('notice.address.edit')) {
            $contact = $this->contactRepo->findByUserId(Auth::id(), $targetId);

            abort_if(
                !$contact,
                Response::HTTP_NOT_FOUND,
                __('message.error.404', ['attribute' => __('label.notice_address')])
            );
            $appInfoKey = self::ACTION_RESULT_KEY_EDIT;
        }

        $contact = $this->contactRepo->store(
            array_merge(
                $request->onlyForStore(),
                ['id' => $targetId, 'user_id' => Auth::id()]
            )
        );

        return redirect(route('notice.address.edit', ['id' => $contact->id]))
            ->with($appInfoKey, true);
    }

    /**
     * Send the verify request email.
     *
     * @param $contactId
     * @return Redirector|Application|RedirectResponse
     */
    public function sendVerification($contactId): Redirector|Application|RedirectResponse
    {
        $contact = $this->contactRepo->findByUserId(Auth::id(), $contactId);

        abort_if(
            !$contact,
            Response::HTTP_NOT_FOUND,
            __('message.error.404', ['attribute' => __('label.notice_address')])
        );

        if (!$contact->enableSendVerify()) {
            abort(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                __(
                    'message.error.send_verify_request',
                    ['minutes' => config('specs.send_contacts_verify_interval')]
                )
            );
        }

        if (!$this->contactRepo->sendVerifyRequest($contact->id)) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return redirect(route('notice.address.list'))->with('verify_requested', true);
    }

    /**
     * Delete the specified contacts.
     *
     * @param $contactId
     * @return Application|RedirectResponse|Redirector
     */
    public function delete($contactId): Redirector|RedirectResponse|Application
    {
        $contact = $this->contactRepo->findByUserId(Auth::id(), $contactId);

        if (!$contact) {
            abort('404', __('message.error.404', ['attribute' => __('label.notice_address')]));
        }

        if (!$this->contactRepo->delete($contactId, Auth::id())) {
            abort('500');
        }

        return redirect(route('notice.address.list'))
            ->with('deleted', true);
    }

    /**
     * Mark the contacts to verified, and notify to the user.
     * TODO: change 500 error to validation error
     *
     * @param $id
     * @return Application|Factory|\Illuminate\Contracts\View\View
     */
    public function verify($id): \Illuminate\Contracts\View\View|Factory|Application
    {
        $contact = $this->contactRepo->verify($id);

        /**
         * FIXME:
         *  - Set supportable message,
         *  - Notice to the requester user ?
         */
        abort_if((!$contact || !$contact->user), Response::HTTP_INTERNAL_SERVER_ERROR);

        $contact->sendVerifiedNotification();

        return view('panels.message', [
            'pageTitle' => __('label.page_title.verify_email'),
            'messages' => [__('message.support.thanks_verified')],
            'linkItems' => [['text' => __('label.link.about'), 'href' => route('about')]]
        ])->with('verified', true);
    }

    /**
     * Render the verify request mail for preview.
     *
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function previewVerify($id, Request $request): mixed
    {
        $validParam = $this->getValidPreviewParameters($request);

        $contact = $this->contactRepo->findByUserId(Auth::id(), $id);
        if (!$contact) {
            abort(Response::HTTP_NOT_FOUND, __('message.error.404', ['attribute' => __('label.notice_address')]));
        }

        /**
         * It's set 'contact_name' if the passed value is a valid.
         */
        $contact->name = Arr::get($validParam, 'contact_name', null) ?? $contact->name;

        $mail = new VerifyRequestContactsNotification($contact);

        $mockNotifiable = Contact::factory()->make([
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => 'mock@example.com',
            'user_id' => Auth::id(),
        ]);

        return $mail->renderAsMarkdown($mockNotifiable);
    }

    /**
     * Validate request and return only valid parameters.
     * FIXME: 'id' is no check.
     *
     * @param Request $request
     * @return array
     */
    private function getValidPreviewParameters(Request $request): array
    {
        $parameters = ['contact_name'];

        $validator = Validator::make(
            $request->only($parameters),
            ContactStoreRequest::rulesPreviewMail()
        );

        return $this->onlyValidParameters($request, $validator, $parameters);
    }
}
