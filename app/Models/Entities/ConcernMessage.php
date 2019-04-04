<?php

namespace App\Models\Entities;

use App\Notifications\AlertNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

/**
 * Class ConcernMessage
 *
 * NOTE: This entity is depends on the config and lang data.
 * @see lang/{locale}/alert.php
 * @see config/alert.php
 *
 * @package App\Models\Entities
 */
class ConcernMessage
{
    const VIEW_KEY_FORMAT = 'emails.alert.template.%s';

    /**
     * The data of the message outlines.
     * NOTE: 'user_id' is 'devices.owner_id', but not used. :)
     *
     * @var array
     */
    protected $fillableBase = [
        'id', 'view_id', 'name', 'subject', 'user_id',
    ];

    /**
     * The data for the message body contents.
     *
     * @var array
     */
    protected $fillableContent = [
        'device_name', 'device_user_name', 'device_reported_at',
        'rule_time_limits', 'rule_notify_times', 'rule_embedded_message',
    ];

    /**
     * The identifier.
     * @var
     */
    private $id;

    /**
     * The string of reference the view
     * @var
     */
    private $viewKey;

    /**
     * The base data.
     * @var
     */
    protected $base = [];

    /**
     * The content data.
     * @var
     */
    protected $content = [];

    /**
     * ConcernMessage constructor.
     * @param $bindData
     */
    public function __construct($bindData)
    {
        if (!is_array($bindData)) {
            $bindData = [];
        }

        // Set base data. from parameter or lang file.

        $this->id = Arr::get($bindData, 'id', 0);

        foreach ($this->fillableBase as $column) {
            $langKey = sprintf('alert.template.%s.%s', $this->id, $column);

            $this->base[$column] = Arr::get($bindData, $column, null) ?? __($langKey);
        }

        $this->viewKey = sprintf(self::VIEW_KEY_FORMAT, $this->base['view_id']);
    }

    /**
     * Getter for the 'base'
     *
     * @return array
     */
    public function getBaseData()
    {
        return $this->base;
    }

    /**
     * Setter for the 'content'
     *
     * @param Device $device
     * @param Rule $rule
     * @return $this
     */
    public function setContent($device, $rule)
    {
        $this->content['device_name'] = $device->name;
        $this->content['device_user_name'] = $device->user_name;
        $this->content['device_reported_at'] = $device->reported_at ?? '';

        $this->content['rule_time_limits'] = $rule->time_limits;
        $this->content['rule_notify_times'] = $rule->notify_times;
        $this->content['rule_embedded_message'] = $rule->embedded_message ?? '';

        return $this;
    }

    /**
     * Merge 'content' properties by the given data.
     * NOTE: Pass only the valid parameters.
     *
     * @param $data
     * @return $this
     */
    public function mergeContent($data)
    {
        foreach ($this->fillableContent as $key) {
            if (array_key_exists($key, $data)) {
                $this->content[$key] = Arr::get($data, $key, '');
            }
        }

        return $this;
    }

    /**
     * Set the message content by use mock data.
     * @see lang/{locale}/alert.php
     *
     * @return $this
     */
    public function buildContentMock()
    {
        $this->contant = [];

        foreach ($this->fillableContent as $item) {
            $langKey = sprintf('alert.mock.%s', $item);
            $this->content[$item] = __($langKey);
        }

        return $this;
    }

    /**
     * Return the new AlertNotification instance.
     *
     * @return AlertNotification|null
     */
    public function buildNotification()
    {
        if (!View::exists($this->viewKey)) {
            Log::error('Not exists view [%key]', ['%key' => $this->viewKey]);
            return null;
        }

        return new AlertNotification(
            $this->viewKey,
            $this->base['subject'],
            $this->content
        );
    }

    /**
     * Render the notification mail as HTML.
     *
     * @param null $notifiable
     * @return mixed
     *
     * @throws \HttpException
     */
    public function renderAsMarkDown($notifiable = null)
    {
        if (!$notifiable) {
            $notifiable = factory(Contact::class)->make([
                'email' => __('alert.mock.to_email'),
                'name' => __('alert.mock.to_name'),
            ]);
        }

        $alertMail = $this->buildNotification();
        if (!$alertMail) {
            throw new \HttpException('', 404);
        }

        return $alertMail->renderAsMarkdown($notifiable);
    }

    /**
     * Return the properties as array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            ['id' => $this->id, 'view' => $this->viewKey],
            $this->base,
            $this->content
        );
    }
}
