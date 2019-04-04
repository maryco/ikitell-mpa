<?php
namespace App\Models\Entities;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeviceDashboard
{
    /**
     * @var Device
     */
    protected $device = null;

    /**
     * @var Rule
     */
    protected $rule = null;

    /**
     * @var Alert
     */
    protected $alert = null;

    /**
     * @var bool
     */
    private $isDemo = false;

    /**
     * DeviceDashboard constructor.
     *
     * @param Device $device
     */
    public function __construct(Device $device)
    {
        $this->device = $device;

        $this->rule = (!is_null($device->rule_id))
            ? $device->rule
            : (new Rule)->fillDefault();

        $this->alert = $device->alert;
    }

    /**
     * Getter 'device'.
     *
     * @return Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Getter 'rule'.
     *
     * @return Rule|mixed
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Setter 'isDemo'.
     *
     * @param bool $isDemo
     * @return $this
     */
    public function setDemo($isDemo)
    {
        $this->isDemo = $isDemo;
        return $this;
    }

    /**
     * Getter 'isDemo'.
     *
     * @return bool
     */
    public function isDemo()
    {
        return $this->isDemo;
    }

    /**
     * Return converted variables for the use front interface.
     *
     * @return array
     */
    public function toArray()
    {
        $data = [
            'id' => $this->device->id,
            'image' => $this->device->getImage(),
            'name' => $this->device->name,
            'resetWord' => $this->device->getResetWord(__('label.btn.reset_timer')),

            'lastResetAt' =>  (is_null($this->device->reported_at)) ? ''
                : $this->device->getReportedDateTime(false)->format('Y-m-d H:i'),

            'isAlert' => $this->hasAlert(),
            'isSuspend' => $this->device->isSuspend(),
            'enableReset' => $this->device->enableReport(),

            'isDemo' => $this->isDemo,
        ];
        //Log::debug('Dashbord data => ', ['' => $data]);

        return array_merge($data, $this->getTimerDetail());

        // TODO: Delete below array structure.
        // (It's for the gone iOS app.)
        /*
        return [
            "lastResetTime" =>  $this->getLastResetTime(),
            "setting" => [
                "timeLimit"  => ($this->rule) ? $this->rule->timer_interval : 0,
            ],
            "alerts" => [
                [
                    "id" =>  1,
                    "to" => [
                        ["name" => "My friend A", "email" => "friend001@ikitell.me"],
                        ["name" => "My friend B", "email" => "friend002@ikitell.me"]
                    ],
                    "sendCount" => 1,
                    "subject" => "[TEST] Notification from ikitell",
                    "body" => "Hi, This message is test!!",
                    "sendAt" => "2017-02-12 15:00:00"
                ]
            ]
        ];
        */
    }

    /**
     * Return whether alert existence on this device.
     *
     * @return bool
     */
    public function hasAlert()
    {
        return !is_null($this->alert) || boolval($this->device->in_alert) === true;
    }

    /**
     * Generate device's timer related data,
     * depends on device and rule settings.
     *
     * @return array
     */
    public function getTimerDetail()
    {
        $baseDate = $this->device->getReportedDateTime();

        $limitHour = $this->rule->time_limits;
        $limitDate = Carbon::instance($baseDate)->addHours($limitHour);
        $remainingHour = $limitHour - $baseDate->diffInHours(now());

        $data = [
            'baseDate' => $baseDate->format('Y-m-d H:i'),
            'remainingTime' => $remainingHour,
            'limitTime' => $limitHour,
            'resetLimitAt' => $limitDate->format('Y-m-d H:i'),
        ];

        return $data;
    }
}
