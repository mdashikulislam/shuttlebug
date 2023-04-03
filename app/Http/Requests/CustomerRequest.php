<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
            'email'         => 'required|string|email|max:190|unique:users,email,'.$this->route('id'),
            'mobile'        => 'required',
//            'inv_email'     => 'required',
            'street'        => 'required',
            'suburb'        => 'required',
            'city'          => 'required',
            'geo'           => 'required'
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
            'email.unique'      => "This email already exists.",
            'mobile.required'   => "The mobile number is needed for sms.",
            'geo.required'      => "The map location is required"
        ];
    }
}
