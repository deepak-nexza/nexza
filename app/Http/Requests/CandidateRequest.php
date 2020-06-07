<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
class CandidateRequest extends FormRequest
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
        $request = request();
        $rules = [ 
            'email' => ['required', 'isvalidchar','regex:/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/'],
            'contact_number' => ['required', 'isvalidchar','regex:/^[7-9][0-9]{9}$/'],
            'full_name' => ['required', 'isvalidchar','regex:/[A-Za-z0-9]/'],
            'address' => ['required'],
        ];
        return $rules;
    }

    public function messages()
    {
         $messages =  [
            'email.required' => trans('message.required',['field'=>preg_replace('/_/',' ','email')]),
            'contact_number.required' => trans('message.required',['field'=>preg_replace('/_/',' ','contact_number')]),       
            'full_name.required' => trans('message.required',['field'=>preg_replace('/_/',' ','full_name')]),       
            'address.required' => trans('message.required',['field'=>preg_replace('/_/',' ','address')]),       
        ];
        return $messages;
    }
}
