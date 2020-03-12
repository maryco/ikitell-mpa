<?php

namespace App\Models\Entities;

use App\Notifications\DeviceResumedNotification;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordMail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'plan', 'ban'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();

        // Solution for the unique-constraint of 'email'.
        self::deleted(function ($model) {
            $model->email = sprintf('%s_%s', $model->email, now()->timestamp);
            $model->save();
        });
    }

    /**
     * Scope a query by the email
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function devices()
    {
        return $this->hasMany('App\Models\Entities\Device', 'owner_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany('App\Models\Entities\Contact', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rules()
    {
        return $this->hasMany('App\Models\Entities\Rule', 'user_id');
    }

    /**
     * Return whether the users.plan is 'limited'.
     *
     * @return bool
     */
    public function isLimited()
    {
        return intval($this->plan) === intval(config('codes.subscription_types.limited'));
    }

    /**
     * Return users.plan as string.
     *
     * @return string
     */
    public function getPlanName()
    {
        $filtered = array_filter(
            config('codes.subscription_types'),
            function ($code) {
                return intval($code) === intval($this->plan);
            }
        );

        if (count($filtered) !== 1) {
            return 'unknown';
        }

        return Arr::first(array_keys($filtered)) ?: 'unknown';
    }

    /**
     * Return the limit of max notify targets depends on users.plan.
     *
     * @return int
     */
    public function getMaxNotifyTargets()
    {
        $key = sprintf('specs.notify_targets_max.%s', $this->getPlanName());
        return config($key, 0);
    }

    /**
     * Return the limit of the max device making depends on users.plan.
     *
     * @return int
     */
    public function getMaxMakingDevice()
    {
        $key = sprintf('specs.making_device_max.%s', $this->getPlanName());
        return config($key, 0);
    }

    /**
     * Return the limit of the max rule making depends on users.plan.
     *
     * @return int
     */
    public function getMaxMakingRule()
    {
        $key = sprintf('specs.making_rule_max.%s', $this->getPlanName());
        return config($key, 0);
    }

    /**
     * Return the limit of the max contacts making depends on users.plan.
     *
     * @return int
     */
    public function getMaxMakingContacts()
    {
        $key = sprintf('specs.making_contacts_max.%s', $this->getPlanName());
        return config($key, 0);
    }

    /**
     * @see \Illuminate\Auth\MustVerifyEmail::sendEmailVerificationNotification
     */
    public function sendEmailVerificationNotification()
    {
        $verifyMail = new VerifyEmail();

        $verifyMail::toMailUsing(function ($user) {
            $verifyUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(config('specs.verify_limit.account')),
                ['id' => $user->getKey()]
            );

            return (new MailMessage())
                ->subject(__('email.subject.verify_email_register'))
                ->action(__('email.action.do_verify_register'), $verifyUrl)
                ->markdown('emails.auth.verify');
        });

        $this->notify($verifyMail);
    }

    /**
     * @see \Illuminate\Auth\Passwords\CanResetPassword::sendPasswordResetNotification
     */
    public function sendPasswordResetNotification($token)
    {
        $resetPasswordMail = new ResetPasswordMail($token);

        $resetPasswordMail::toMailUsing(function ($user, $token) {
            return (new MailMessage())
                ->subject(__('email.subject.reset_password'))
                ->action(__('email.action.do_reset_password'), url(config('app.url').route('password.reset', $token, false)))
                ->markdown('emails.auth.reset_password')
            ;
        });

        $this->notify($resetPasswordMail);
    }

    /**
     * Send the device resumed notification.
     *
     * @param $device
     * @param $isOwner
     * @return bool
     */
    public function sendDeviceResumedNotification($device, $isOwner = true)
    {
        // Check the user's attribute on this device.
        $deviceUserId = ($isOwner) ? $device->owner_id : $device->assigned_user_id;
        if ($this->id !== $deviceUserId) {
            Log::warning(
                'Not match user\'s attribute on this device. [%user] [%device][%deviceUser]',
                ['%user' => $this->id, '%device' => $device->id, '$deviceUser' => $deviceUserId]
            );
            return false;
        }

        $deviceInfoMail = new DeviceResumedNotification($device);

        $this->notify($deviceInfoMail);

        return true;
    }
}
