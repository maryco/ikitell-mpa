<?php

namespace App\Http\Requests;

use App\Models\Entities\NotificationLog;

class NoticeLogSearchRequest extends BaseSearchRequest
{
    protected $conditionNames = [
        'job_status', 'past', 'sort', 'sort_direction'
    ];

    protected $defaultConditions = [
        'job_status' => NotificationLog::JOB_STATUS_EXECUTED,
        'past' => 30,
        'sort' => 'created_at',
        'sort_direction' => 'desc',
    ];

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
        return [
            //
        ];
    }

    /**
     * @see \App\Http\Requests\BaseSearchRequest::getValidCond
     */
    public function getValidCond($key)
    {
        /**
         * NOTE: Currently all conditions are default only.
         */
        $value = parent::getValidCond($key);

        switch ($key) {
            case 'job_status':
            case 'past':
            case 'sort':
            case 'sort_direction':
                $value = $this->defaultConditions[$key];
                break;
            default:
                break;
        }

        return $value;
    }

    /**
     * Return default conditions.
     *
     * @return array
     */
    public function getDefaultConditions()
    {
        return $this->defaultConditions;
    }
}
