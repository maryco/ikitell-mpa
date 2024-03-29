<?php

namespace App\Models\Entities;

use App\Notifications\AlertNotification;
use Database\Factories\AlertFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use HasFactory;

    /**
     * The type of email address.
     * TODO: Change to Enum
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

    protected $casts = [
        'notify_count' => 'integer',
        'max_notify_count' => 'integer',
    ];

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
                $model->notification_payload = unserialize(
                    $model->notification_payload,
                    ['allowed_classes' => [AlertNotification::class]]
                );
            }

            $model->sendTargetBag = json_decode($model->send_targets, true) ?: [];
        });
    }

    /**
     * @return BelongsTo<Device>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'id');
    }

    /**
     * @return AlertFactory
     */
    protected static function newFactory(): AlertFactory
    {
        return AlertFactory::new();
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
