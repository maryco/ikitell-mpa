<?php

namespace App\Models\Entities;

use App\Enums\Device\DeviceImageType;
use ArrayObject;
use Illuminate\Support\Arr;

trait DeviceImage
{
    /**
     * TODO: remove
     * The image types
     * @var array<string, int>
     */
    protected static array $imageTypes = [
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
     * @param ?string $value
     * @return array|mixed|null
     */
    public static function getPresetImage(string $value = null): mixed
    {
        $presets = [];
        $picked = null;

        // Append unique 'key' for using in front.
        foreach (static::$presetImages as $item) {
            $presets[] = array_merge(
                $item,
                [
                    'key' => sprintf(static::$presetImageKey, $item['value']),
                    'type' => DeviceImageType::PRESET->value,
                ]
            );

            if ((int) $value === (int) $item['value']) {
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
     * @return ArrayObject
     */
    public static function makeImageModel($data, $type = null): ArrayObject
    {
        $model = ['type' => $type ?? DeviceImageType::PRESET->value];

        if ((int) $model['type'] === DeviceImageType::PRESET->value) {
            $model['value'] = Arr::get($data, static::$inputNames['preset'], static::$presetImageDefault);
        }

        /**
         * TODO: Implements "$type is self::IMAGE_TYPE_ORIGINAL"
         */

        return new ArrayObject($model, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Return all image types or specific one.
     *
     * @param ?string $typeName
     * @return array|int|null
     */
    public static function getImageType(string $typeName = null): int|array|null
    {
        return (is_null($typeName))
            ? DeviceImageType::toArray()
            : DeviceImageType::fromName($typeName)?->value;
    }
}
