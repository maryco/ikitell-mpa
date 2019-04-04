<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactStoreRequest;
use App\Models\Entities\Contact;
use App\Models\Repositories\ContactRepositoryInterface;
use App\Notifications\VerifyRequestContactsNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class NoticeAddressController extends Controller
{
    /**
     * @var ContactRepositoryInterface
     */
    protected $contactRepo;

    /**
     * NoticeAddressController constructor.
     * @param ContactRepositoryInterface $contactRepo
     */
    public function __construct(ContactRepositoryInterface $contactRepo)
    {
        $this->middleware('signed')->only('notice.address.verify');
        $this->middleware('throttle:6,1')->only('notice.address.verify');

        $this->contactRepo = $contactRepo;
    }

    /**
     * Get contacts list.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getList()
    {
        $contacts = $this->contactRepo->getByUserId(Auth::id());

        return view('notice.address.list', compact('contacts'));
    }

    /**
     * Show create/edit form.
     *
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showForm($id = null)
    {
        $contact = $this->contactRepo->makeModel();

        if (Route::getCurrentRoute()->named('notice.address.edit')) {
            $contact = $this->contactRepo->findByUserId(Auth::id(), $id);
            if (!$contact) {
                abort('404', __('message.error.notfound', ['attribute' => __('label.notice_address')]));
            }
        }

        // TODO: Implements. (if need)
        //$contactForm = new ContactStoreRequest();

        return view('notice.address.form', compact('contact'));
    }

    /**
     * Store the contacts.
     *
     * @param ContactStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(ContactStoreRequest $request)
    {
        $appInfoKey = 'saved';
        $targetId = Route::getCurrentRoute()->parameter('id', null);

        if (Route::getCurrentRoute()->named('notice.address.edit')) {
            $contact = $this->contactRepo->findByUserId(Auth::id(), $targetId);

            if (!$contact) {
                abort('404', __('message.error.404', ['attribute' => __('label.notice_address')]));
            }

            $appInfoKey = 'edited';
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
     */
    public function sendVerify($contactId)
    {
        $contact = $this->contactRepo->findByUserId(Auth::id(), $contactId);

        if (!$contact) {
            abort('404', __('message.error.404', ['attribute' => __('label.notice_address')]));
        }

        if (!$contact->enableSendVerify()) {
            abort(
                '500',
                __(
                    'message.error.send_verify_request',
                    ['minutes' => config('specs.send_contacts_verify_interval')]
                )
            );
        }

        if (!$this->contactRepo->sendVerifyRequest($contact->id)) {
            abort('500');
        }

        return redirect(route('notice.address.list'))
            ->with('verify_requested', true);
    }

    /**
     * Delete the specified contacts.
     *
     * @param $contactId
     */
    public function delete($contactId)
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
     *
     * @param $id
     */
    public function verify($id)
    {
        $contact = $this->contactRepo->verify($id);

        if (!$contact) {
            /**
             * FIXME:
             *  - Set supportable message,
             *  - Notice to the requester user ?
             */
            abort(500);
        }

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
    public function previewVerify($id, Request $request)
    {
        $validParam = $this->getValidPreviewParameters($request);

        $contact = $this->contactRepo->findByUserId(Auth::id(), $id);
        if (!$contact) {
            abort('404', __('message.error.404', ['attribute' => __('label.notice_address')]));
        }

        /**
         * It's set 'contact_name' if the passed value is a valid.
         */
        $contact->name = Arr::get($validParam, 'contact_name', null) ?? $contact->name;

        $mail = new VerifyRequestContactsNotification($contact);

        $mockNotifiable = factory(Contact::class)->make([
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
     * @param $id
     * @param Request $request
     * @return array
     */
    private function getValidPreviewParameters(Request $request)
    {
        $parameters = ['contact_name'];

        $validator = Validator::make(
            $request->only($parameters),
            ContactStoreRequest::rulesPreviewMail()
        );

        return $this->onlyValidParameters($request, $validator, $parameters);
    }
}
