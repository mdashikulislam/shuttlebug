<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
{
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
            'first_name'    => 'required',
            'last_name'     => 'required',
            'relation'      => 'required',
        ];
    }

    /**
     * Custom messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'relation.required'   => "The role is required.",
        ];
    }
}
