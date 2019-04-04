<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
    protected $table = 'device_log';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'device_id', 'reporting_type',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('user_id', 'id', 'App\Models\Entities\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo('device_id', 'id', 'App\Models\Entities\Device');
    }
}
