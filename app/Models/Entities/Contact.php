<?php

namespace App\Models\Entities;

use App\Notifications\AlertNotification;
use App\Notifications\VerifiedContactsNotification;
use App\Notifications\VerifyRequestContactsNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class Contact extends BaseModel
{
    use SoftDeletes, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'name', 'email', 'description', 'email_verified_at', 'send_verify_at',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Entities\User', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function device()
    {
        return $this->belongsToMany('App\Models\Entities\Device', 'device_contact');
    }

    /**
     * Scope a query by primary key
     *
     * @param $query
     * @param $id
     * @return mixed
     */
    public function scopeId($query, $id)
    {
        return $query->where('id', $id);
    }

    /**
     * Scope a query by user_id
     *
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeUserId($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query verified
     *
     * @param $query
     * @return mixed
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope a query by email
     *
     * @param $query
     * @param $email
     * @return mixed
     */
    public function scopeEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Return whether 'email' is editable.
     *
     * @return bool
     */
    public function enableEditEmail()
    {
        if ($this->isVerified() || $this->send_verify_at !== null) {
            return false;
        }

        return true;
    }

    /**
     * Return whether be able to send the verify request mail.
     *
     * The permit send conditions is below,
     * - Not verified.
     * - Send interval must elapsed from last sent.
     *
     * @return bool
     */
    public function enableSendVerify()
    {
        if ($this->isVerified()) {
            return false;
        }

        if (!$this->getLastVerifyAt()) {
            return true;
        }

        $sinceSendAt = Carbon::parse($this->last_send_verify_at)
            ->diffInMinutes(Carbon::now());

        Log::debug('Minutes of since latest send_verify_at = ', ['' => $sinceSendAt]);

        return $sinceSendAt >= config('specs.send_contacts_verify_interval');
    }

    /**
     * Return the max send_verify_at from user's all contacts.
     *
     * @param bool $withTrash
     * @return string|null
     */
    public function getLastVerifyAt($withTrash = true)
    {
        /**
         * NOTE: Latest send_verify_at includes deleted record.
         * Because prevent doing delete and create for the attempt several send.
         */
        if ($this->last_send_verify_at === null) {
            $query = Contact::where('user_id', $this->user_id);

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
    public function isVerified()
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Return whether the verify request is expired.
     *
     * @return bool
     */
    public function isVerifyExpired()
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
    public function getVerifyStatus()
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
    public function sendVerifyRequestNotification($doCopy = false)
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
    public function sendVerifiedNotification()
    {
        $verifiedMail = new VerifiedContactsNotification($this);

        $user = $this->user()->first();

        if (!$user) {
            Log::warning(
                'Not found notifiable user this contacts. [%userId] [%contactId]',
                ['%userId' => $this->user_id, '%contactId' => $this->id]
            );
            return;
        }

        $user->notify($verifiedMail);
    }
}
