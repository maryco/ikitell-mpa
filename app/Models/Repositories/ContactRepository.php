<?php
namespace App\Models\Repositories;

use App\Models\Entities\Contact;
use App\Models\Entities\DeviceContact;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactRepository implements ContactRepositoryInterface
{
    public function makeModel($bindData = null)
    {
        $model = new Contact();
        if ($bindData) {
            $model->mergeData($bindData);
        }

        return $model;
    }

    public function count()
    {
        return Auth::guest() ? 0 : Contact::userId(Auth::id())->count();
    }

    /**
     * @see \App\Models\Repositories\ContactRepositoryInterface::findByUserId
     */
    public function findByUserId($userId, $contactId)
    {
        return Contact::id($contactId)
            ->userId($userId)
            ->first();
    }

    /**
     * @see \App\Models\Repositories\ContactRepositoryInterface::findByEmail
     */
    public function findByEmail($email)
    {
        return Contact::email($email)->first();
    }

    /**
     * @see \App\Models\Repositories\ContactRepositoryInterface::getByUserId
     */
    public function getByUserId($userId, $onlyAvailable = false)
    {
        $query = Contact::userId($userId);

        // FIXME: Sort order

        if ($onlyAvailable) {
            $query->verified();
            $query->orderByDesc('email_verified_at');
        } else {
            $query->orderByDesc('email_verified_at');
            $query->orderByDesc('send_verify_at');
        }

        return $query->get();
    }

    /**
     * @see \App\Models\Repositories\ContactRepositoryInterface::store
     * @throws \Throwable
     */
    public function store($data)
    {
        return DB::transaction(function () use ($data) {
            if (Arr::get($data, 'id', null)) {
                $rule = $this->findByUserId($data['user_id'], $data['id']);

                // Force remove 'email', if has send verify email.
                if ($rule && !$rule->enableEditEmail()) {
                    Arr::forget($data, 'email');
                }
            } else {
                $rule = $this->makeModel();
            }

            $rule->mergeData($data);

            $rule->save();

            return $rule;
        });
    }

    /**
     * @see \App\Models\Repositories\ContactRepositoryInterface::delete
     * @throws \Throwable
     */
    public function delete($contactId, $userId)
    {
        return DB::transaction(function () use ($contactId, $userId) {
            $contact = Contact::id($contactId)
                ->userId($userId)
                ->lockForUpdate()
                ->first();

            if (!$contact) {
                Log::error(
                    'Not found target contact [%id] [%user]',
                    ['%id' => $contactId, '%user' => $userId]
                );
                return false;
            }

            DeviceContact::contactId($contactId)->delete();

            return $contact->delete();
        });
    }

    /**
     * @see \App\Models\Repositories\ContactRepositoryInterface::sendVerifyRequest
     * @throws \Throwable
     */
    public function sendVerifyRequest($contactId)
    {
        return DB::transaction(function () use ($contactId) {
            $contact = Contact::id($contactId)
                ->lockForUpdate()
                ->first();

            if (!$contact) {
                Log::error('Not found target contact [%id]', ['%id' => $contactId]);
                return false;
            }

            $contact->sendVerifyRequestNotification(true);

            $contact->send_verify_at = Carbon::now();

            $contact->save();

            return true;
        });
    }

    /**
     * @see \App\Models\Repositories\ContactRepositoryInterface::verify()
     * @throws \Throwable
     */
    public function verify($id)
    {
        return DB::transaction(function () use ($id) {
            $contact = Contact::id($id)
                ->lockForUpdate()
                ->first();

            if (!$contact) {
                Log::error('Not found target contact [%id]', ['%id' => $id]);
                return null;
            }

            if ($contact->isVerified()) {
                Log::error(
                    'Already verified [%id] [%at]',
                    ['%id' => $id, '%at' => $contact->email_verified_at]
                );
                return null;
            }

            if ($contact->isVerifyExpired()) {
                Log::error(
                    'Expired for verification [%id] [%send_at]',
                    ['%id' => $id, '%send_at' => $contact->send_verify_at]
                );
                return null;
            }

            $contact->email_verified_at = Carbon::now();
            $contact->save();

            return $contact;
        });
    }
}
