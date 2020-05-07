@extends('layouts.app')
@section('content') 

        {!!
        Form::open(
        array(
        'name' => 'NexzaForms',
        'id' => 'NexzaForms',
        'autocomplete' => 'off',
        'class'=>' otp',
        )
        )
        !!}
        
                  
                <div class="modal-body iframeCss">
                    <div class="row">
                    <div  class="col-md-12">
                        <div class="msghtml"></div>
                            <h1>{{ trans('headings.please_authenticate') }}</h1>
                            <p class="mb20">{{ trans('headings.new_one_time_pass') }}</p>
                            <div class="form-group mb-0">
                            <label class="fieldLabel" for="otp_vals">{{ trans('headings.one_time_password') }}</label>
                             {!!
                            Form::tel('otp_vals',
                            null,
                            array('required',
                            'class'=>'form-control number numberCls required',
                            'maxlength'=>6,
                            'id'=>'otp_vals',
                            'placeholder' => trans('otp.otp_number'),
                            'title' => trans('otp.otp_fld_title2')

                            ))
                            !!}
                        </div>
                    </div>
                    </div>
                </div>
                <div class="modal-footer iframeCss">
                    <div class="col-12 text-right">
                        <a href="javascript:void(0);" title="Resend Code" class="btn-link unLine" id="resent_code">{{ trans('form.resend_code') }}</a>
                        {!! Form::
                        button(
                        trans('application_labels.button.submit'),
                        array(
                        'id'=>'2','class'=>'btn  btn-primary otpsubmit fr'

                        )
                        )
                        !!}
                    </div>
                </div>
                </div>
                
   <!-- Promo code expiry modal-->
    @include('partials.form.promo_code_expiry')
        {!! Form::close() !!}
  
@endsection
@section('jscript')

{{-- When a success record insert operation takes place, close the modal --}}

{{-- When a success record insert operation takes place, close the modal --}}
@if(isset($operation_status))
@var $operation_status = session()->get('operation_status', false);

@if ($operation_status == config('b2c_common.YES'))
<script>
    try {
        var p = window.parent;

        // Set message in parent window

        // Try to refresh data table in it's current page
        if (typeof p.oTable !== 'undefined') {
            p.oTable.draw('page');
        } else {
            //p.location.reload();
        }

        // Close modal window
        p.jQuery('#loginmodal').modal('hide');
    } catch (e) {
        if (typeof console !== 'undefined') {
            console.log(e);
        }
    }


</script>
@endif
@endif
{{-- End close modal --}}
<script src="{{ asset('js/jquery.validate.js') }}"></script>
<script>
var messages = {
    valid_email_format: "{{ trans('error_message.login.valid_email_format') }}",
    req_old_minlength: "{{ trans('error_message.req_old_minlength') }}",
    req_pas_minlength: "{{ trans('error_message.req_pas_minlength') }}",
    req_conf_minlength: "{{ trans('error_message.req_conf_minlength') }}",
    req_old_maxlength: "{{ trans('error_message.req_old_maxlength') }}",
    req_pas_maxlength: "{{ trans('error_message.req_pas_maxlength') }}",
    req_conf_maxlength: "{{ trans('error_message.req_conf_maxlength') }}",
    same_as_password: "{{ trans('error_message.same_as_password') }}",
    valid_password_format: "{{ trans('error_message.password_regex') }}",
    valid_cpassword_format: "{{ trans('error_message.cpassword_regex') }}",
    save_profile_url : "{{ URL::route('save_profile') }}",
    _token: "{{ csrf_token() }}",
    otp_url: "{{ URL::route('otp_validate') }}",
    ajax_image: "{{ asset('/images/ajax-loader.gif') }}",
    resend_url: "{{ URL::route('resend_otp') }}",
    validate_email_id: "{{ URL::route('validate_email_id') }}",
    otp_sent: "{{ Lang::get('error_message.otp_sent') }}",
    otp_blank: "{{ Lang::get('error_message.otp_blank') }}",
    otp_not_correct: "{{ trans('error_message.otp_not_correct') }}",
    otp_inactive: "{{ Lang::get('error_message.otp_inactive') }}",
    otp_expire: "{{ Lang::get('error_message.otp_expire') }}",
    email_sent: "{{ Lang::get('error_message.email_sent')}}",
    otp_attempt_left: "{{ trans('error_message.otp_attempt_left')}}",
    otp_max_limit: "{{ trans('error_message.otp_max_limit')}}",
    email_attempt_left: "{{ trans('error_message.reg.attempt_left')}}",
    email_max_limit: "{{ trans('error_message.reg.max_limit')}}",
    otp_authenticated : "{{ trans('error_message.otp_authenticated') }}",
    login_url : "{{ URL::route('login')}}",
    exception_error: "{{trans('error_message.exception_error')}}",
    save_auth_url: "{{URL::route('save_authuser')}}",
    login_auth_url: "{{URL::route('login_authenticate')}}",
    otp_authentication: "{{URL::route('otp_authentication')}}",
    otp_submit_last_attempt : "{{ trans('error_message.otp_submit_last_attempt') }}",
    otp_submit_max_limit : "{{ trans('error_message.otp_submit_max_limit') }}",
    otp_max_attempt : "{{ trans('error_message.otp_max_attempt') }}",
    promo_code_expired: "{{ trans('error_message.promo_code_expired') }}",
    otp_resent : "{{ trans('error_message.otp_resent') }}",
};
alert(messages.resend_url);
</script>
<script src="{{ asset('js/otp.js') }}"></script>
@endsection