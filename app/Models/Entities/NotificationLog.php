<?php

namespace App\Models\Entities;


class NotificationLog extends BaseModel
{
    /**
     * The queue job status.
     */
    const JOB_STATUS_RESERVED = 1;
    const JOB_STATUS_EXECUTED = 2;
    const JOB_STATUS_FAILED = 3;
    const JOB_STATUS_UNKNOWN = 9;

    /**
     * @var string
     */
    protected $table = 'notification_log';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'alert_id', 'device_id', 'contact_id', 'notify_count',
        'email', 'name', 'content', 'job_status'
    ];

    /**
     * @var array
     */
    public static $outline = [
        'id', 'alert_id', 'device_id', 'contact_id', 'notify_count',
        'email', 'name', 'job_status', 'created_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo('App\Models\Entities\Device', 'device_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact()
    {
        return $this->belongsTo('App\Models\Entities\Contact', 'contact_id', 'id');
    }

    /**
     * Scope a query by the primary
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
     * Scope a query by the job_status
     *
     * @param $query
     * @param $status
     * @return mixed
     */
    public function scopeJobStatus($query, $status)
    {
        return $query->where('job_status', $status);
    }

    /**
     * @return array
     */
    public static function getJobStatus()
    {
        return [
            self::JOB_STATUS_RESERVED,
            self::JOB_STATUS_EXECUTED,
            self::JOB_STATUS_FAILED,
            self::JOB_STATUS_UNKNOWN,
        ];
    }

    /**
     * Return 'notify_count' with units.
     *
     * @return String
     */
    public function getNotifyCountString()
    {
        return  ($this->notify_count ?? '0') .__('label.unit.times');
    }
}
