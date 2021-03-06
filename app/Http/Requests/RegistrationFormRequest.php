<?php 
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
class RegistrationFormRequest extends Request
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
        dd(21);
        $rules = [ 
            'biz_name' => ['required', 'isvalidchar','min:3'],
            'contact_number' => ['required', 'isvalidchar'],
            'email' => ['required', 'isvalidchar', 'email', 'unique:users', 'min:8', 'max:65'],
            'password' => ['required', 'isvalidchar', 'regex:/^(?!.*(.)\1\1)(?=.*[A-Z])(?=.*[\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,])(?=.*[a-z])(?=.*[0-9])[A-Za-z0-9\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,]{8,50}$/'],
            'password_confirmation' => ['required', 'isvalidchar', 'same:password', 'regex:/^(?!.*(.)\1\1)(?=.*[A-Z])(?=.*[\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,])(?=.*[a-z])(?=.*[0-9])[A-Za-z0-9\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,]{8,50}$/'],
        ];
        return $rules;
    }

    /**
     * Validation Messages
     */
     public function messages()
    {
        return [
            'password.required' => trans('error_message.req_new_pass'),
            'password_confirmation.required' => trans('error_message.req_conf_pass'),
            'password.regex' => trans('error_message.password_regex'),
            'password_confirmation.regex' => trans('error_message.cpassword_regex'),
            'password_confirmation.same' => trans('error_message.same_as_password'),
            'password.different' => trans('error_message.different_pass'),
            'email.required' => trans('error_message.reg.valid_email_req'),
            'email.email' => trans('error_message.reg.valid_email_format'),
            'email.checkdomain' => trans('error_message.reg.valid_email_format'),
            'biz_name.required' => trans('error_message.create_customer.biz_name_required'),          
            'biz_name.min' => trans('error_message.create_customer.biz_name_min'),
            'contact_number.required' => trans('error_message.create_customer.contac_required'),
        ];
    }
}
