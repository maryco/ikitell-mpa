<?php

namespace App\Http\Requests;

use App\Models\Entities\Device;
use App\Models\Repositories\DeviceRepository;
use App\Rules\MaxStored;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class DeviceStoreRequest extends BaseStoreRequest
{
    /**
     * Structures for the front interface.
     */
    const FRONT_MODELS = [
        'rule' => [
            'value' => '',
            'text' => '',
            'time_limits' => 0,
            'notify_times' => 0,
        ],

        'contact' => [
            'value' => '',
            'text' => '',
            'isPop' => false,
        ],
    ];

    protected $ignoreInputRegex = '/^device_/';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Rules for the only create.
        $createRule = [];
        if (Route::getCurrentRoute()->getName() === 'device.create') {
            $createRule['device_total'] = [
                'required',
                new MaxStored(
                    new DeviceRepository(),
                    Auth::user()->getMaxMakingDevice()
                ),
            ];
        }

        return array_merge(
            [
                'device_name' => 'required|string|max:200',
                'device_user_name' => 'nullable|string|max:50',
                'device_image_preset' => ['nullable', Rule::in(data_get($this->getPresetImages(), '*.value'))],
                'device_description' => 'nullable|string|max:300',
                'device_reset_word' => 'nullable|string|max:20',
                'device_suspend_start_at' => 'nullable|date_format:Y-m-d|after_or_equal:today',
                'device_suspend_end_at' => 'nullable|date_format:Y-m-d|after_or_equal:device_suspend_start_at',

                'device_rule_id' => [
                    'required',
                    'string',
                    Rule::exists('rules', 'id')->where(function ($query) {
                        $query->where('user_id', Auth::id());
                    }),
                ],

                'device_notification_targets' => [
                    'nullable',
                    'array',
                    'max:' . Auth::user()->getMaxNotifyTargets(),
                    Rule::exists('contacts', 'id')->where(function ($query) {
                        $query->where('user_id', Auth::id())
                            ->whereNotNull('email_verified_at');
                    }),
                ],
            ],
            $createRule
        );
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        $messages['device_suspend_start_at.after_or_equal']
            = __('validation.after_or_equal', ['date' => __('label.today')]);

        return $messages;
    }

    /**
     * @see \App\Http\Requests\BaseStoreRequest::onlyForStore
     */
    public function onlyForStore()
    {
        $data = parent::onlyForStore();

        /**
         * TODO:
         * - Type is set to 'pc' force in release beta.
         * - Can edit devices are user type 'basic' or 'business'.
         */
        $data['owner_id'] = Auth::id();
        $data['type'] = config('codes.device_types.pc');

        return $data;
    }

    /**
     * Convert model to array
     * for the Vue component parameter.
     *
     * @param $rules
     * @return array
     */
    public static function rulesToArray($rules)
    {
        $data = [];

        foreach ($rules as $rule) {
            $data[] = [
                'value' => $rule->id,
                'text' => $rule->name,
                'time_limits' => $rule->getTimeLimitsDays(),
                'notify_times' => $rule->notify_times,
            ];
        }

        return $data;
    }

    /**
     * Convert model to array
     * for the Vue component parameter.
     *
     * @param $contacts
     * @param $bindContactIds
     * @return array
     */
    public static function contactsToArray($contacts, $bindContactIds)
    {
        $data = [];

        foreach ($contacts as $contact) {

            $isPop = false;
            if ($bindContactIds) {
                foreach ($bindContactIds as $bindContactId) {
                    if ($contact->id == $bindContactId) {
                        $isPop = true;
                        break;
                    }
                }
            }

            $data[] = [
                'value' => $contact->id,
                'text' => $contact->name,
                'isPop' => $isPop,
            ];
        }

        return $data;
    }

    /**
     * Get all preset image values.
     *
     * @return array|mixed|null
     */
    public function getPresetImages()
    {
        return Device::getPresetImage();
    }
}
