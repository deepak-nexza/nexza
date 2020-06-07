<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
class ProfileRequest extends FormRequest
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
            'email' => ['required', 'isvalidchar', 'email', 'uniqueData:nex_user,email', 'min:8', 'max:65'],
            'phone' => ['required', 'isvalidchar','uniquPhone:nex_user,phone,','regex:/^[7-9][0-9]{9}$/'],
            'password' => ['required', 'isvalidchar', 'regex:/^(?!.*(.)\1\1)(?=.*[A-Z])(?=.*[\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,])(?=.*[a-z])(?=.*[0-9])[A-Za-z0-9\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,]{8,50}$/'],
            'password_confirmation' => ['required', 'isvalidchar', 'same:password', 'regex:/^(?!.*(.)\1\1)(?=.*[A-Z])(?=.*[\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,])(?=.*[a-z])(?=.*[0-9])[A-Za-z0-9\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,]{8,50}$/'],
        ];
        return $rules;
    }

    public function messages()
    {
         $messages =  [
            'password.required' => trans('error_message.req_new_pass'),
            'password_confirmation.required' => trans('error_message.req_conf_pass'),
            'password.regex' => trans('error_message.password_regex'),
            'password_confirmation.regex' => trans('error_message.cpassword_regex'),
            'password_confirmation.same' => trans('error_message.same_as_password'),
            'password.different' => trans('error_message.different_pass'),
            'email.required' => trans('error_message.reg.valid_email_req'),
            'email.email' => trans('error_message.reg.valid_email_format'),
            'email.checkdomain' => trans('error_message.reg.valid_email_format'),
            'email.unique_data' => trans('Emil has already been taken'),
            'phone.uniqu_phone' => trans('Phone has already been taken'),
            'phone.required' => trans('Phone Required'),          
            'phone.isvalidchar' => trans('\\\\'),
        ];
        return $messages;
    }
}
