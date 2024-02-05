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
    public const FRONT_MODELS = [
        'message' => [
            'value' => '',
            'text' => '',
            'subject' => '',
        ],
    ];

    protected string $ignoreInputRegex = '/^rule_/';

    /**
     * The mail template models.
     * @see \App\Models\Entities\ConcernMessage
     *
     * @var array<int, mixed>
     */
    protected array $mailMessages = [];

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
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

        if (Route::getCurrentRoute()?->getName() === 'rule.create') {
            $createRules = [
              'rule_total' => [
                  'required',
                  new MaxStored(new RuleRepository, Auth::user()?->getMaxMakingRule())
              ],
            ];
        }

        return array_merge($baseRules, $createRules);
    }

    /**
     * Get the validation rules, for the mail preview.
     *
     * @param $messagesIds
     * @return array<string, mixed>
     */
    public static function rulesPreviewMail($messagesIds): array
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
     * @param mixed $mailMessages
     * @return $this
     */
    public function setMailMessages(mixed $mailMessages): static
    {
        if (is_array($mailMessages)) {
            $this->mailMessages = $mailMessages;
        }

        return $this;
    }

    /**
     * Return the range for the time limits values.
     *
     * @return array<int>
     */
    public static function getTimeLimitsValues(): array
    {
        return range(
            config_int('specs.time_limit_min', 24),
            config_int('specs.time_limit_max', 48),
            24
        );
    }

    /**
     * Return the range for the notify times values.
     *
     * @return array<int>
     */
    public static function getNotifyTimesValues(): array
    {
        return range(
            0,
            config_int('specs.send_notice_max.'.Auth::user()?->getPlanName(), 1),
            1
        );
    }

    /**
     * Convert model to array
     * for the Vue component parameter.
     *
     * @return array<int, mixed>
     */
    public function messagesToArray(): array
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
