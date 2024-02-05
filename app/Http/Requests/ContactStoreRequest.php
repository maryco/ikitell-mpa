<?php

namespace App\Http\Requests;

use App\Models\Repositories\ContactRepository;
use App\Rules\MaxStored;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class ContactStoreRequest extends BaseStoreRequest
{
    protected string $ignoreInputRegex = '/^contact_/';

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
        $rules = [
            'contact_name' => 'required|string|max:50',
            'contact_description' => 'nullable|string|max:300',
        ];

        $emailRules = ['required','email','max:200', 'not_in:'.auth_provided_user()->email];

        if (Route::getCurrentRoute()?->getName() === 'notice.address.create') {
            // Rules for create.
            $rules['contact_total'] = [
                'required',
                new MaxStored(
                    new ContactRepository(),
                    auth_provided_user()?->getMaxMakingContacts()
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
            // Rules for edit.
            $rules['contact_email'] = array_merge(
                $emailRules,
                [
                    Rule::unique('contacts', 'email')->where(function ($query) {
                        $query->whereNull('deleted_at');
                    })->ignore(Route::getCurrentRoute()?->parameter('id'))
                ]
            );
        }

        return $rules;
    }

    /**
     * Get the validation rules, for the preview
     * of the verify request mail.
     *
     * @return array<string, string>
     */
    public static function rulesPreviewMail(): array
    {
        return [
            'contact_name' => 'nullable|string|max:50',
        ];
    }

    /**
     * @see \Illuminate\Foundation\Http\FormRequest::messages
     */
    public function messages(): array
    {
        return [
            'not_in' => __('validation.custom.email.duplicate_account')
        ];
    }
}
