<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
class EventTicketRequest extends FormRequest
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
        if(!empty($request->get('event_uid'))){
            $required = '';
        }else{
             $required = 'required';
        }
        $rules['title'] = $required;
        $rules['type'] = $required.'|numeric';
        $rules['event_space'] = $required.'|numeric';
        $rules['amt_per_person'] = $required.'|numeric';
        $rules['message'] = $required;
        $rules['booking_duration'] ='regex:/[\d]{4}[\-][\d]{2}[\-][\d]{2}[\s]+[\d]{1,12}[:][\d]{1,12}[\s]+[A-Za-z]{2}[\s+][\-\s]+[\d]{4}[\-][\d]{2}[\-][\d]{2}[\s]+[\d]{1,12}[:][\d]{1,12}[\s]+[A-Za-z]{2}/';
        $rules['event_type'] = $required;
        return $rules;
    }

    public function messages()
    {
        $messages = [];
        $messages['title.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Event Heading'))]);
        $messages['type.numeric'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Ticket Type'))]);
        $messages['type.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Ticket Type'))]);
        $messages['event_space.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Total Booking Space Available'))]);
        $messages['event_space.numeric'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Total Booking Space Available'))]);
        $messages['amt_per_person.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Booking Amount per individual'))]);
        $messages['amt_per_person.numeric'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Booking Amount per individual'))]);
        $messages['message.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Message'))]);
        $messages['message.numeric'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Message'))]);
        $messages['booking_duration.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Booking Start/End Date'))]);
        $messages['event_type.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','event_type'))]);
        return $messages;
    }
    
}
