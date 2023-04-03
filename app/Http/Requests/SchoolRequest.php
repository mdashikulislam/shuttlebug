<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolRequest extends FormRequest
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
            'name'      => 'required',
            'dropfrom'  => 'required|not_in:00:00',
            'dropby'    => 'required|not_in:00:00',
            'street'    => 'required',
            'suburb'    => 'required',
            'city'      => 'required',
            'geo'       => 'required'
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
            'geo.required' => "The map location is required",
            'dropfrom.required' => "Needed for trip planning",
            'dropfrom.not_in' => "Needed for trip planning",
            'dropby.required' => "Needed for trip planning",
            'dropby.not_in' => "Needed for trip planning",
        ];
    }
}
