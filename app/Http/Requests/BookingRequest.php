<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
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
            'edit.*.puloc_id'       => 'required_with:edit.*.doloc_id',
            'edit.*.doloc_id'       => 'required_with:edit.*.puloc_id',
            'create.*.puloc_id'     => 'required_with:create.*.doloc_id',
            'create.*.doloc_id'     => 'required_with:create.*.puloc_id',
        ];
    }

    /**
     * Custom validator checks
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ( isset($this->input['edit']) ) {
                foreach ( $this->input('edit') as $id => $edit ) {
                    // non-home pickup without time
                    if ( $edit['puloc_id'] > 300000 && $edit['putime'] == '' ) {
                        $validator->errors()->add("edit.$id.putime", "This pickup time is required");
                    }
                    // both time fields empty
                    if ( $edit['putime'] == '' && $edit['dotime'] == '' ) {
                        // no pickup
                        if ( $edit['puloc_id'] == '' ) {
                            $validator->errors()->add("edit.$id.putime", "At least 1 time is required");
                            $validator->errors()->add("edit.$id.dotime", "At least 1 time is required");
                        } elseif ( $edit['doloc_id'] > 300000 ) {
                            $validator->errors()->add("edit.$id.dotime", "This dropoff time is required");
                        }
                    }
                    // both time fields have time
                    if ( $edit['putime'] > '' && $edit['dotime'] > '' ) {
                        $validator->errors()->add("edit.$id.dotime", "There is a pickup time, remove this");
                    }
                }
            }

            if ( isset($this->input['create']) ) {
                foreach ( $this->input('create') as $id => $create ) {
                    if ( count(array_filter($create)) > 0 ) {
                        // non-home pickup without time
                        if ( $create['puloc_id'] > 300000 && $create['putime'] == '' ) {
                            $validator->errors()->add("create.$id.putime", "This pickup time is required");
                        }
                        // both time fields empty
                        if ( $create['putime'] == '' && $create['dotime'] == '' ) {
                            // no pickup
                            if ( $create['puloc_id'] == '' ) {
                                $validator->errors()->add("create.$id.putime", "At least 1 time is required");
                                $validator->errors()->add("create.$id.dotime", "At least 1 time is required");
                            } elseif ( $create['doloc_id'] > 300000 ) {
                                $validator->errors()->add("create.$id.dotime", "This dropoff time is required");
                            }
                        }
                        // both time fields have time
                        if ( $create['putime'] > '' && $create['dotime'] > '' ) {
                            $validator->errors()->add("create.$id.dotime", "There is a pickup time, remove this");
                        }
                    }
                }
            }
        });
    }

    /**
     * Custom messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'edit.*.puloc_id.required_with'       => 'This pickup is required',
            'edit.*.doloc_id.required_with'       => 'This dropoff is required',
            'create.*.puloc_id.required_with'     => 'This pickup is required',
            'create.*.doloc_id.required_with'     => 'This dropoff is required',
        ];
    }
}
