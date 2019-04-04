<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class Rule extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'name', 'description',
        'time_limits', 'notify_times', 'message_id', 'embedded_message'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function device()
    {
        return $this->hasMany('App\Models\Entities\Device', 'rule_id');
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
     * Scope a query by primary
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
     * Fill default values.
     *
     * @return $this
     */
    public function fillDefault()
    {
        $this->time_limits = \Config::get('specs.time_limit_min');
        $this->notify_times = \Config::get('specs.send_notice_max.basic');

        return $this;
    }

    /**
     * Get time_limits as a days
     *
     * @return mixed
     */
    public function getTimeLimitsDays()
    {
        return $this->time_limits > 0  ? intval($this->time_limits / 24) : 0;
    }
}
