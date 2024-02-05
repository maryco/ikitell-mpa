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
     * @inheritDoc
     */
    public function findByUserId($userId, $contactId): ?Contact
    {
        return Contact::id($contactId)
            ->userId($userId)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function findByEmail($email)
    {
        return Contact::email($email)->first();
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function store($data): Contact
    {
        return DB::transaction(function () use ($data) {
            if ($id = Arr::get($data, 'id')) {
                $contact = $this->findByUserId($data['user_id'], $id);

                // Force remove 'email', if has send verify email.
                if ($contact && !$contact->enableEditEmail()) {
                    Arr::forget($data, 'email');
                }
            } else {
                $contact = $this->makeModel();
            }

            $contact->mergeData($data);
            $contact->save();

            return $contact;
        });
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function sendVerifyRequest($contactId): bool
    {
        return DB::transaction(static function () use ($contactId) {
            $contact = Contact::id($contactId)
                ->lockForUpdate()
                ->first();

            if (!$contact) {
                Log::error('Not found target contact [%id]', ['%id' => $contactId]);
                return false;
            }

            $contact->send_verify_at = Carbon::now();
            $contact->save();
            $contact->sendVerifyRequestNotification(true);

            return true;
        });
    }

    /**
     * @inheritDoc
     */
    public function verify($id): ?Contact
    {
        return DB::transaction(static function () use ($id) {
            $contact = Contact::id($id)
                ->lockForUpdate()
                ->first();

            if (!$contact) {
                Log::error('Not found target contact', ['contactId' => $id]);
                return null;
            }

            if ($contact->isVerified()) {
                Log::error(
                    'Already verified',
                    ['contactId' => $id, 'verifiedAt' => $contact->email_verified_at]
                );
                return null;
            }

            if ($contact->isVerifyExpired()) {
                Log::error(
                    'Expired for verification',
                    ['contactId' => $id, 'sendVerifyAt' => $contact->send_verify_at]
                );
                return null;
            }

            $contact->email_verified_at = Carbon::now();
            $contact->save();

            return $contact;
        });
    }
}
