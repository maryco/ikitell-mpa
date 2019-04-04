<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseStoreRequest extends FormRequest
{
    /**
     * The remove regex for the form input element name.
     * @var
     */
    protected $ignoreInputRegex;

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
     * Return only form input values in request
     * NOTE: Convert key if has a replace pattern.
     *
     * @return array
     */
    public function onlyForStore()
    {
        $input = [];

        $formInput = $this->only(array_keys($this->rules()));

        if (empty($this->ignoreInputRegex)) {
            return $formInput;
        }

        foreach ($formInput as $name => $value) {
            /**
             * Remove prefix
             */
            $key = preg_replace($this->ignoreInputRegex, '', $name);
            $input[$key] = $value;
        }

        return $input;
    }
}
