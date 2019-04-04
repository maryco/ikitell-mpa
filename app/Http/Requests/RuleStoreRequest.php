<?php

namespace App\Http\Requests;

use App\Models\Repositories\MessageRepository;
use App\Models\Repositories\RuleRepository;
use App\Rules\MaxStored;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class RuleStoreRequest extends BaseStoreRequest
{
    /**
     * The structures for the front interface.
     */
    const FRONT_MODELS = [
        'message' => [
            'value' => '',
            'text' => '',
            'subject' => '',
        ],
    ];

    protected $ignoreInputRegex = '/^rule_/';

    /**
     * The mail template models.
     * @see \App\Models\Entities\ConcernMessage
     *
     * @var
     */
    protected $mailMessages = [];

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
        // FIXME:
        if (count($this->mailMessages) === 0) {
            $msgRepo = new MessageRepository();
            $this->setMailMessages($msgRepo->getTemplate());
        }

        $baseRules = [
            'rule_name' => 'required|string|max:200',
            'rule_description' => 'nullable|string|max:300',
            'rule_time_limits' => ['required', Rule::in($this::getTimeLimitsValues())],
            'rule_notify_times' => ['required', Rule::in($this::getNotifyTimesValues())],
            'rule_embedded_message' => 'nullable|string|max:200',
            'rule_message_id' => ['required', Rule::in(data_get($this->mailMessages, '*.id'))],
        ];

        $createRules = [];

        if (Route::getCurrentRoute()->getName() === 'rule.create') {
            $createRules = [
              'rule_total' => ['required', new MaxStored(
                  new RuleRepository,
                  Auth::user()->getMaxMakingRule()
              )],
            ];
        }

        return array_merge($baseRules, $createRules);
    }

    /**
     * Get the validation rules, for the mail preview.
     *
     * @param $messagesIds
     * @return array
     */
    public static function rulesPreviewMail($messagesIds)
    {
        return [
            'rule_time_limits' => ['nullable', Rule::in(self::getTimeLimitsValues())],
            'rule_notify_times' => ['nullable', Rule::in(self::getNotifyTimesValues())],
            'rule_embedded_message' => 'nullable|string|max:200',
            'rule_message_id' => ['required', Rule::in($messagesIds)],
        ];
    }

    /**
     * Setter for the 'mailMessages'
     *
     * @param array $mailMessages
     * @return $this
     */
    public function setMailMessages($mailMessages)
    {
        if (is_array($mailMessages)) {
            $this->mailMessages = $mailMessages;
        }

        return $this;
    }

    /**
     * Return the range for the time limits values.
     *
     * @return array
     */
    public static function getTimeLimitsValues()
    {
        return range(
            config('specs.time_limit_min'),
            config('specs.time_limit_max'),
            24
        );
    }

    /**
     * Return the range for the notify times values.
     *
     * @return array
     */
    public static function getNotifyTimesValues()
    {
        return range(
            0,
            config('specs.send_notice_max.'.Auth::user()->getPlanName()),
            1
        );
    }

    /**
     * Convert model to array
     * for the Vue component parameter.
     *
     * @param $messages
     * @return array
     */
    public function messagesToArray()
    {
        $data = [];

        foreach ($this->mailMessages as $message) {
            $data[] = [
                'value' => $message->id,
                'text' => $message->name,
                'subject' => $message->subject,
            ];
        }

        return $data;
    }

}
