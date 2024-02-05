<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseStoreRequest extends FormRequest
{
    /**
     * The remove regex for the form input element name.
     * @var string
     */
    protected string $ignoreInputRegex;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Return only form input values in request
     * NOTE: Convert key when has pattern for replace.
     *
     * @return array<string, mixed>
     */
    public function onlyForStore(): array
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
