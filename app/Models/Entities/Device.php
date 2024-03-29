<?php

namespace App\Models\Entities;

use ArrayObject;
use Carbon\Carbon;
use Database\Factories\DeviceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends BaseModel
{
    use SoftDeletes, DeviceImage, HasFactory;

    /**
     * The cache key for users devices.
     * (Cache only 'device.id')
     */
    public const CACHE_KEY_USER_DEVICES = 'device:user:%s';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'owner_id', 'assigned_user_id', 'type', 'passport_client_id',
        'rule_id', 'name', 'description', 'reset_word', 'in_alert', 'in_suspend',
        'user_name', 'reported_at', 'report_reserved_at', 'suspend_start_at', 'suspend_end_at',
    ];

    /**
     * @var array
     */
    protected $guarded = ['image'];

    /**
     * The attributes that should be mutated to dates.
     * NOTE: reported_at and report_reserved_at is integer(timestamp)
     *
     * @var array
     */
    protected $dates = [
        'deleted_at', 'suspend_start_at', 'suspend_end_at'
    ];

    /**
     * @return DeviceFactory
     */
    protected static function newFactory(): DeviceFactory
    {
        return DeviceFactory::new();
    }

    /**
     * @var null|ArrayObject
     */
    protected ?ArrayObject $imageModel = null;

    protected static function boot()
    {
        parent::boot();

        /**
         * TODO: Device suspend is not support a time level setting.
         * Need modify this the implements when support a time.
         */

        self::saving(function ($model) {
            if ($model->suspend_start_at !== null) {
                $model->suspend_start_at = Carbon::parse($model->suspend_start_at)
                    ->format('Y-m-d 00:00:00');
            }

            if ($model->suspend_end_at !== null) {
                $model->suspend_end_at = Carbon::parse($model->suspend_end_at)
                    ->format('Y-m-d 23:59:59');
            }

            if ($model->imageModel instanceof ArrayObject) {
                $model->forceFill(['image' => json_encode($model->imageModel->getArrayCopy()) ?: null]);
            }
        });

        self::retrieved(function ($model) {
            $model->loadImageModel();
        });

        self::created(function ($model) {
            $model->clearCache();
        });

        self::deleted(function ($model) {
            $model->clearCache();
        });
    }

    /**
     * @return BelongsTo<User, Device>
     */
    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    /**
     * @return BelongsTo<User, Device>
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'id');
    }

    /**
     * @return BelongsTo<Rule>
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class, 'rule_id', 'id');
    }

    /**
     * NOTE: If device has a alert,
     * don't create new record until active one has delete.
     *
     * @return HasOne<Alert>
     */
    public function alert(): HasOne
    {
        return $this->hasOne(Alert::class, 'device_id', 'id');
    }

    /**
     * @return BelongsToMany<Contact>
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'device_contact');
    }

    /**
     * Scope a query by the primary key
     *
     * @param $query
     * @param $deviceId
     * @return mixed
     */
    public function scopeId($query, $deviceId)
    {
        return $query->where('id', $deviceId);
    }

    /**
     * Scope a query by the id and user_id
     *
     * @param $query
     * @param $id
     * @param $userId
     * @return mixed
     */
    public function scopeOwnedByUser($query, $id, $userId)
    {
        return $query->where('id', $id)
            ->where('owner_id', $userId);
    }

    /**
     * Scope a query by the owner_id or assigned_user_id
     * depends on user plan
     *
     * @param $query
     * @param $user
     * @return mixed
     */
    public function scopeUserColumn($query, $user)
    {
        return ($user->isLimited())
            ? $query->where('assigned_user_id', $user->id)
            : $query->where('owner_id', $user->id);
    }

    /**
     * Scope a query by the owner user_id
     *
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeOwner($query, $userId)
    {
        return $query->where('owner_id', $userId);
    }

    /**
     * Scope a query by the assigned user_id
     *
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeAssignedUser($query, $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    /**
     * Clear the columns using for the system suspend and resume.
     * NOTE: This method not clear 'suspend_start_at' and 'suspend_end_at'.
     *
     * @param $force
     * @return $this
     */
    public function clearSystemSuspend($force = true)
    {
        if ($force || !$this->isSuspend()) {
            $this->in_suspend = false;
            $this->report_reserved_at = null;
        }

        return $this;
    }

    /**
     * Return reset_word or specified default string.
     *
     * @param null $default
     * @return mixed|null
     */
    public function getResetWord($default = null)
    {
        if ($default) {
            return (empty($this->reset_word)) ? $default : $this->reset_word;
        }

        return $this->reset_word;
    }

    /**
     * Checking the device.type code by the given key string.
     *
     * @param $key
     * @return bool
     */
    public function isType($key)
    {
        $types = config('codes.device_types', []);
        if (!array_key_exists($key, $types)) {
            return false;
        }

        return intval($this->type) === intval($types[$key]);
    }

    /**
     * Return Carbon instance from 'reported_at'
     *
     * NOTE: Return current datetime if has no 'reported_at' (default).
     *
     * @param bool $defaultNow
     * @return Carbon|null
     */
    public function getReportedDateTime($defaultNow = true)
    {
        if (!is_null($this->reported_at)) {
            return Carbon::createFromTimestamp($this->reported_at);
        }

        return ($defaultNow) ? Carbon::now() : null;
    }

    /**
     * Whether passed or not the report interval time
     * since current reported time.
     *
     * @return bool
     */
    public function enableReport(): bool
    {
        if (is_null($this->reported_at)) {
            return true;
        }

        $sinceReportedAt = Carbon::createFromTimestamp($this->reported_at)
            ->diffInMinutes(Carbon::now());

        return $sinceReportedAt >= config('specs.device_report_interval');
    }

    /**
     * Whether passed or not the reserved reporting time.
     *
     * @return bool
     */
    public function enableReservedReport(): bool
    {
        if (is_null($this->report_reserved_at)) {
            return false;
        }

        return Carbon::now()->getTimestamp() > (int)$this->report_reserved_at;
    }

    /**
     * Check the device is suspended.
     *
     * NOTE: The judge depends on suspend_start_at and suspend_end_at.
     * (Ignore is_suspend)
     *
     * @return bool
     */
    public function isSuspend(): bool
    {
        if (!$this->suspend_start_at && !$this->suspend_end_at) {
            return false;
        }

        $startAt = ($this->suspend_start_at) ? Carbon::parse($this->suspend_start_at) : null;
        $endAt = ($this->suspend_end_at) ? Carbon::parse($this->suspend_end_at) : null;

        if ($startAt && $endAt) {
            return Carbon::today()->between($startAt, $endAt);
        }
        if ($startAt) {
            return Carbon::today()->greaterThanOrEqualTo($startAt);
        }
        if ($endAt) {
            return Carbon::today()->lessThan($endAt);
        }
        return false;
    }

    /**
     * Checking the time limits from 'reported_at'
     * with specified hour.
     *
     * @param $limitHour
     * @return bool
     */
    public function isTimeOver($limitHour)
    {
        $reportedDate = $this->getReportedDateTime(false);

        if (!$reportedDate) {
            return false;
        }

        return Carbon::now()->getTimestamp() > $reportedDate->addHours($limitHour)->getTimestamp();
    }

    /**
     * Set.
     *
     * @param ArrayObject $model
     */
    public function setImageModel(ArrayObject $model): void
    {
        $this->imageModel = $model;
    }

    /**
     * Decode json and set to property 'imageModel' as a ArrayObject
     *
     * @return ArrayObject|null
     */
    public function loadImageModel()
    {
        $this->imageModel = (!is_null($this->image))
            ? new ArrayObject(json_decode($this->image, true), ArrayObject::ARRAY_AS_PROPS)
            : null;

        return $this->imageModel;
    }

    /**
     * Get the image data as array.
     * NOTE: The available image type is 'preset' only.
     *
     * @return array|mixed|null
     */
    public function getImage()
    {
        if (is_null($this->loadImageModel())) {
            return null;
        }

        return (intval($this->imageModel->type) === intval(static::$imageTypes['preset']))
            ? self::getPresetImage($this->imageModel->value)
            : null;
    }

    /**
     * Filled 'user_name' if it's empty.
     *
     * @param $owner
     * @param $assigned
     * @return $this
     */
    public function fillUserName($owner, $assigned)
    {
        if ($this->user_name) {
            return $this;
        }

        if ($assigned) {
            $this->user_name = $assigned->name ?: __('label.default.device.user_name');
            return $this;
        }

        if ($owner) {
            $this->user_name = $owner->name ?: __('label.default.device.user_name');
            return $this;
        }

        return $this;
    }

    /**
     * Clear this devices all cached data.
     *
     * @param $key
     */
    protected function clearCache()
    {
        $key = sprintf(self::CACHE_KEY_USER_DEVICES, $this->owner_id);
        parent::removeCache($key);

        if ($this->assigned_user_id) {
            $key = sprintf(self::CACHE_KEY_USER_DEVICES, $this->assigned_user_id);
            parent::removeCache($key);
        }
    }
}
