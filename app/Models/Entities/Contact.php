<?php

namespace App\Models\Entities;

use App\Notifications\VerifiedContactsNotification;
use App\Notifications\VerifyRequestContactsNotification;
use Carbon\Carbon;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class Contact extends BaseModel
{
    use SoftDeletes, Notifiable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'description',
        'email_verified_at',
        'send_verify_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'email_verified_at', 'send_verify_at'];

    /**
     * @var null
     */
    protected $last_send_verify_at = null;

    protected static function boot()
    {
        parent::boot();

//        // NOTE: The'email' has no unique-constraint.
//        self::deleted(function ($model) {
//            $model->email = sprintf('%s_%s', $model->email, now()->timestamp);
//            $model->save();
//        });
    }

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return BelongsToMany<Device>
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'device_contact');
    }

    /**
     * @return ContactFactory
     */
    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }

    /**
     * Scope a query by primary key
     *
     * @param Builder<Contact> $query
     * @param int $id
     */
    public function scopeId(Builder $query, int $id): void
    {
        $query->where('id', $id);
    }

    /**
     * Scope a query by user_id
     *
     * @param Builder<Contact> $query
     * @param int $userId
     */
    public function scopeUserId(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Scope a query verified
     *
     * @param Builder<Contact> $query
     */
    public function scopeVerified(Builder $query): void
    {
        $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope a query by email
     *
     * @param Builder<Contact> $query
     * @param string $email
     */
    public function scopeEmail(Builder $query, string $email): void
    {
        $query->where('email', $email);
    }

    /**
     * Return whether 'email' is editable.
     *
     * @return bool
     */
    public function enableEditEmail(): bool
    {
        return is_null($this->send_verify_at) && !$this->isVerified();
    }

    /**
     * Returns whether verification request emails can be sent.
     *
     * Conditions for permission to send:
     * - Not verified.
     * - A certain interval has passed since the last attempt.
     *
     * @return bool
     */
    public function enableSendVerify(): bool
    {
        if ($this->isVerified()) {
            return false;
        }

        if (!$this->getLastVerifyAt()) {
            return true;
        }

        $sinceSendAt = Carbon::parse($this->last_send_verify_at)->diffInMinutes(Carbon::now());

        Log::debug('Minutes of since latest send_verify_at = ', ['' => $sinceSendAt]);

        return $sinceSendAt >= config('specs.send_contacts_verify_interval');
    }

    /**
     * Return the max send_verify_at from user's all contacts.
     *
     * @param bool $withTrash
     * @return string|null
     */
    public function getLastVerifyAt(bool $withTrash = true): ?string
    {
        /**
         * NOTE: Latest send_verify_at includes deleted record.
         * Because prevent doing delete and create for the attempt several send.
         */
        if ($this->last_send_verify_at === null) {
            $query = self::where('user_id', $this->user_id);

            if ($withTrash) {
                $query->withTrashed();
            }

            $this->last_send_verify_at = $query->max('send_verify_at');
        }

        return $this->last_send_verify_at;
    }

    /**
     * Return whether the contacts has verified.
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Return whether the verify request is expired.
     *
     * @return bool
     */
    public function isVerifyExpired(): bool
    {
        if ($this->isVerified() || $this->send_verify_at === null) {
            return false;
        }

        $sinceSendAt = Carbon::parse($this->send_verify_at)
            ->diffInMinutes(Carbon::now());

        return $sinceSendAt > config('specs.verify_limit.contacts');
    }

    /**
     * Return the verify status.
     *
     * @return string
     */
    public function getVerifyStatus(): string
    {
        if ($this->isVerified()) {
            return __('label.verified');
        }

        if ($this->isVerifyExpired()) {
            return __('label.verify_expired');
        }

        return __('label.verify_waiting');
    }

    /**
     * Send the verify request URL to the contacts.email
     *
     * @param bool $doCopy  (Notify same mail also to the user)
     */
    public function sendVerifyRequestNotification(bool $doCopy = false): void
    {
        $verifyMail = new VerifyRequestContactsNotification($this);

        $this->notify($verifyMail);

        if ($doCopy && $this->user) {
            $this->user->notify($verifyMail);
        }
    }

    /**
     * Send the verified notification to the contacts.user.
     */
    public function sendVerifiedNotification(): void
    {
        $verifiedMail = new VerifiedContactsNotification($this);

        $user = $this->user()->first();

        if (!$user) {
            Log::warning(
                'Not found notifiable user this contacts',
                ['userId' => $this->user_id, 'contactId' => $this->id]
            );
            return;
        }

        $user->notify($verifiedMail);
    }
}
