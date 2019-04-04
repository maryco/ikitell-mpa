<?php

namespace App\Models\Entities;

use Illuminate\Support\Arr;

trait DeviceImage
{
    /**
     * The image types
     */
    protected static $imageTypes = [
        'preset' => 1,
        'original' => 2,
    ];

    /**
     * The format of unique key for the frontend.
     */
    protected static $presetImageKey = 'presetImg%03d';

    /**
     * The names of form input element.
     *
     * @var array
     */
    protected static $inputNames = [
        'preset' => 'image_preset',
    ];

    /**
     * The default 'value' of preset image.
     */
    protected static $presetImageDefault = 1;

    /**
     * The preset image variables.
     * 'ref' is the attribute of the svg use tag.
     */
    protected static $presetImages = [
        ['ref' => 'mobile', 'class' => 'device-icon-mobile-1', 'value' => '1',],
        ['ref' => 'mobile', 'class' => 'device-icon-mobile-2', 'value' => '2',],
        ['ref' => 'mobile', 'class' => 'device-icon-mobile-3', 'value' => '3',],
        ['ref' => 'mobile', 'class' => 'device-icon-mobile-4', 'value' => '4',],
        ['ref' => 'robot', 'class' => 'device-icon-robot-1', 'value' => '5',],
        ['ref' => 'robot', 'class' => 'device-icon-robot-2', 'value' => '6',],
        ['ref' => 'apple', 'class' => 'device-icon-apple-1', 'value' => '7',],
        ['ref' => 'apple', 'class' => 'device-icon-apple-2', 'value' => '8',],
    ];

    /**
     * Return specific preset image dataset.
     * NOTE: Return all items as array, if the parameter is null.
     *
     * @param null $value
     * @return array|mixed|null
     */
    public static function getPresetImage($value = null)
    {
        $presets = [];
        $picked = null;

        // Append unique 'key' for the front using.
        foreach (static::$presetImages as $idx => $item) {
            $presets[] = array_merge(
                $item,
                [
                    'key' => sprintf(static::$presetImageKey, $item['value']),
                    'type' => static::$imageTypes['preset']
                ]
            );

            if (strval($value) === strval($item['value'])) {
                $picked = Arr::last($presets);
            }
        }

        return ($value === null) ? $presets : $picked;
    }

    /**
     * Make image data model.
     * NOTE: Support only type 'preset'.
     *
     * @param  $data
     * @param $type
     * @return \ArrayObject
     */
    public static function makeImageModel($data, $type = null)
    {
        $model = ['type' => $type ?? static::$imageTypes['preset']];

        if (intval($model['type']) === intval(static::$imageTypes['preset'])) {
            $model['value'] = Arr::get($data, static::$inputNames['preset'], static::$presetImageDefault);
        }

        /**
         * TODO: Implements "$type is self::IMAGE_TYPE_ORIGINAL"
         */

        return new \ArrayObject($model, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Return all image types or specific one.
     *
     * @param null $typeName
     * @return array|mixed
     */
    public static function getImageType($typeName = null)
    {
        return ($typeName === null)
            ? static::$imageTypes
            : Arr::get(static::$imageTypes, $typeName, null);
    }
}
