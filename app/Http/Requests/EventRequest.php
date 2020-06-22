<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
class EventRequest extends FormRequest
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
        $rules['event_name'] = $required;
        $rules['event_type'] = $required.'|numeric';
        $rules['country_id'] = $required.'|numeric';
        $rules['state_id'] = $required.'|numeric';
        if(!empty($request->get('banner_image'))){
            
            $rules['banner_image'] = $required;
        }
        $rules['event_duration'] ='regex:/[\d]{4}[\-][\d]{2}[\-][\d]{2}[\s]+[\d]{1,12}[:][\d]{1,12}[\s]+[A-Za-z]{2}[\s+][\-\s]+[\d]{4}[\-][\d]{2}[\-][\d]{2}[\s]+[\d]{1,12}[:][\d]{1,12}[\s]+[A-Za-z]{2}/';
        $rules['event_privacy'] = $required.'|numeric';
        $rules['event_location'] = $required;
        if(!empty($request->get('description'))) { $rules['description'] = $required; }
        $rules['gst'] = $required.'|numeric|between:0,3';
        return $rules;
    }

    public function messages()
    {
        $messages = [];
        $messages['event_name.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','event_heading'))]);
        $messages['event_type.numeric'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','event_type'))]);
        $messages['event_type.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','event_type'))]);
        $messages['country_id.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Coutnry'))]);
        $messages['country_id.numeric'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','Coutnry'))]);
        $messages['state_id.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','State'))]);
        $messages['state_id.numeric'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','state'))]);
        $messages['banner_image.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','banner_image'))]);
        $messages['event_duration.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','event_duration'))]);
        $messages['event_privacy.numeric'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','event_privacy'))]);
        $messages['event_privacy.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','event_privacy'))]);
        $messages['event_location.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','event_location'))]);
        $messages['event_location.alpha'] = trans('message.alpha',['field'=>strtoupper(preg_replace('/_/',' ','event_location'))]);
        $messages['description.alpha'] = trans('message.alpha',['field'=>strtoupper(preg_replace('/_/',' ','description'))]);
        $messages['description.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','description'))]);
        $messages['gst.required'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','gst'))]);
        $messages['gst.numeric'] = trans('message.required',['field'=>strtoupper(preg_replace('/_/',' ','gst'))]);
        $messages['gst.between'] = trans('Invalid entry for gst');
        return $messages;
    }
    
}
