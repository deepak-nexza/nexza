<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
class EventRequestForDesc extends FormRequest
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
        $rules['event_duration'] ='regex:/[\d]{4}[\-][\d]{2}[\-][\d]{2}[\s]+[\d]{1,12}[:][\d]{1,12}[\s]+[A-Za-z]{2}[\s+][\-\s]+[\d]{4}[\-][\d]{2}[\-][\d]{2}[\s]+[\d]{1,12}[:][\d]{1,12}[\s]+[A-Za-z]{2}/';
        return $rules;
    }

    public function messages()
    {
        $messages = [];
        $messages['description.alpha'] = trans('message.alpha',['field'=>strtoupper(preg_replace('/_/',' ','description'))]);
        $messages['description.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','description'))]);
        return $messages;
    }
    
}
