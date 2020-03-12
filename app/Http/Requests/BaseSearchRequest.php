<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class BaseSearchRequest extends FormRequest
{
    /**
     * The names of the search form input elements.
     *
     * @var array
     */
    protected $conditionNames = [];

    /**
     * The default search conditions.
     *
     * @var array
     */
    protected $defaultConditions = [];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
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
     * Validate and return valid or default search condition value.
     *
     * NOTE:
     * This method validate only 'sort_direction',
     * Override and implements other validations in subclass.
     *
     * @param $key
     * @return mixed
     */
    public function getValidCond($key)
    {
        $value = $this->get($key, null);

        switch ($key) {
            case 'sort_direction':
                $value = (in_array($value, ['desc', 'asc'], true)) ? $value
                    : Arr::get($this->defaultConditions, 'sort_direction', 'desc');
                break;

            default:
                // Clear value if not found in the conditions array.
                $value = in_array($key, $this->conditionNames, true) ? $value : null;
                break;
        }

        return $value;
    }

    /**
     * Return only valid search conditions.
     *
     * @return array
     */
    public function getConditions()
    {
        $conditions = [];
        foreach ($this->conditionNames as $key) {
            $conditions[$key] = $this->getValidCond($key);
        }

        return $conditions;
    }
}
