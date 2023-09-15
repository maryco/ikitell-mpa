<?php

namespace App\Models\Entities;

use Database\Factories\DeviceContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceContact extends Model
{
    use HasFactory;

    protected $table = 'device_contact';

    protected $fillable = ['device_id', 'contact_id'];

    /**
     * @return DeviceContactFactory
     */
    protected static function newFactory(): DeviceContactFactory
    {
        return DeviceContactFactory::new();
    }

    /**
     * Scope query by device_id
     *
     * @param $query
     * @param $deviceId
     * @return mixed
     */
    public function scopeDeviceId($query, $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Scope query by contact_id
     *
     * @param $query
     * @param $contactId
     * @return mixed
     */
    public function scopeContactId($query, $contactId)
    {
        return $query->where('contact_id', $contactId);
    }
}
