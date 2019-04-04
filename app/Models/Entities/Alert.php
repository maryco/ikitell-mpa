<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Alert extends Model
{
    /**
     * The type of email address.
     */
    const TARGET_TYPE_OWNER = 1;
    const TARGET_TYPE_USER = 2;
    const TARGET_TYPE_CONTACTS = 3;

    /**
     * The attributes that are mass assignable.
     * NOTE: next_notify_at is integer(timestamp)
     *
     * @var array
     */
    protected $fillable = ['device_id', 'notify_count',
        'max_notify_count', 'next_notify_at', 'notification_payload'];

    /**
     * @var array
     */
    protected $guarded = ['send_targets'];

    /**
     * The target of sending emails.
     * ['email', 'name', 'type']
     *
     * @var array
     */
    private $sendTargetBag = [];

    protected static function boot()
    {
        parent::boot();


        self::saving(function ($model) {
            if ($model->notification_payload) {
                $model->notification_payload = serialize($model->notification_payload);
            }

            $model->forceFill(['send_targets' => json_encode($model->sendTargetBag) ?: null]);
        });

        self::retrieved(function ($model) {
            if ($model->notification_payload) {
                $model->notification_payload = unserialize($model->notification_payload);
            }

            $model->sendTargetBag = json_decode($model->send_targets, true) ?: [];
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo('App\Models\Entities\Device', 'device_id', 'id');
    }

    /**
     * Scope a query by the primary key
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
     * Add the send target array.
     *
     * @param $email
     * @param $name
     * @param $type
     */
    public function addSendTarget($email, $name, $type = self::TARGET_TYPE_CONTACTS)
    {
        $this->sendTargetBag[] = [
            'email' => $email,
            'name' => $name,
            'type' => $type,
        ];
    }

    /**
     * The getter of 'sendTargetBag'
     *
     * @return array
     */
    public function getSendTarget()
    {
        return $this->sendTargetBag;
    }
}
