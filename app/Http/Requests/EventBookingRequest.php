<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventBookingRequest extends FormRequest
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
            'edit.*.puloc'       => 'required_with:edit.*.doloc',
            'edit.*.putime'      => 'required_with:edit.*.puloc',
            'edit.*.doloc'       => 'required_with:edit.*.puloc',
            'edit.*.passengers'  => 'required_with:edit.*.puloc',
            'edit.*.tripfee'     => 'required_with:edit.*.puloc',
            'create.*.puloc'     => 'required_with:create.*.doloc',
            'create.*.putime'    => 'required_with:create.*.puloc',
            'create.*.doloc'     => 'required_with:create.*.puloc',
            'create.*.passengers'=> 'required_with:create.*.puloc',
            'create.*.tripfee'   => 'required_with:create.*.puloc',
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
            'edit.*.puloc.required_with'        => 'This pickup is required',
            'edit.*.doloc.required_with'        => 'This dropoff is required',
            'edit.*.putime.required_with'       => 'Required',
            'edit.*.passengers.required_with'   => 'Required',
            'edit.*.tripfee.required_with'      => 'Required',
            'create.*.puloc.required_with'      => 'This pickup is required',
            'create.*.doloc.required_with'      => 'This dropoff is required',
            'create.*.putime.required_with'     => 'Required',
            'create.*.passengers.required_with' => 'Required',
            'create.*.tripfee.required_with'    => 'Required',
        ];
    }
}
