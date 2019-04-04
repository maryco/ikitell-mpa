<?php

namespace App\Http\Requests;

use App\Models\Repositories\ContactRepository;
use App\Rules\MaxStored;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class ContactStoreRequest extends BaseStoreRequest
{
    protected $ignoreInputRegex = '/^contact_/';

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
        $rules = [
            'contact_name' => 'required|string|max:50',
            'contact_description' => 'nullable|string|max:300',
            'contact_email' => [],
        ];

        $emailRules = ['required','email','max:200', 'not_in:'.Auth::user()->email];

        if (Route::getCurrentRoute()->getName() === 'notice.address.create') {
            // Rules for the create.
            $rules['contact_total'] = [
                'required',
                new MaxStored(
                    new ContactRepository(),
                    Auth::user()->getMaxMakingContacts()
                ),
            ];

            /**
             * FIXME:
             * If just append Rule::unique rule to the array,
             * Method Illuminate\Validation\Validator::validateUnique:contacts,email,NULL,id does not exist
             */
            $rules['contact_email'] = array_merge(
                $emailRules,
                [
                    Rule::unique('contacts', 'email')->where(function ($query) {
                        $query->whereNull('deleted_at');
                    })
                ]
            );
        } else {
            // Rules for the edit.
            $rules['contact_email'] = array_merge(
                $emailRules,
                [
                    Rule::unique('contacts', 'email')->where(function ($query) {
                        $query->whereNull('deleted_at');
                    })->ignore(Route::getCurrentRoute()->parameter('id'))
                ]
            );
        }

        return $rules;
    }

    /**
     * Get the validation rules, for the preview
     * of the verify request mail.
     *
     * @return array
     */
    public static function rulesPreviewMail()
    {
        return [
            'contact_name' => 'nullable|string|max:50',
        ];
    }

    /**
     * @see \Illuminate\Foundation\Http\FormRequest::messages
     */
    public function messages()
    {
        return [
            'not_in' => __('validation.custom.email.duplicate_account')
        ];
    }
}
