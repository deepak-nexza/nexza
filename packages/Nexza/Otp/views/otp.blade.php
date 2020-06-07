   <!-- otpreg modal -->


    <div class="modal fade" id="otpreg" tabindex="-1" role="dialog" aria-hidden="true" onload="setOtpPopupTag()">
        {!!
        Form::open(
        array(
        'name' => 'NexzaForms',
        'id' => 'NexzaForms',
        'autocomplete' => 'off',
        'class'=>'formElement otp',
        )
        )
        !!}
        <div class="modal-dialog modal-md" style="padding:10px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close icon-delete" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button> 
                </div>
                <div class="modal-body">
                    <div class="row">
                    <div  class="col-md-12">
                        <div class="msghtml"></div>                        
                        <p class="mb10">{{ trans('message.otpwindow') }}</p>
                        <div class="form-group mb-0">
                            <label class="fieldLabel" for="otp_vals">{{ trans('message.one_time_password_code') }}</label>
                            {!!
                            Form::tel('otp_vals',
                            null,
                            array('required',
                            'class'=>'form-control number numberCls required',
                            'placeholder'=>trans('message.otp_number'),
                            'maxlength'=>6,
                            'id'=>'otp_vals',
                            'title' => trans('otp.otp_fld_title2')

                            ))
                            !!}
                            
                            {!! 
                            Form::hidden('ioBB',
                            '',
                            [
                            'id'=>'ioBB'
                            ])
                            !!}
                        </div>

                    </div>
                    </div>
                </div>
                <div class="modal-footer iframeCss">
                    <div class="row">
                        <div class="col text-right">
                            <a href="javascript:void(0);" title="Resend Code" class="btn-link unLine" id="resent_code">{{ trans('message.resend_button') }}</a>
                        {!! Form::
                        button('Submit',
                        array(
                        'id'=>'1',
                        'class'=>'btn  btn-primary otpsubmit fr',
                        )
                        )
                        !!}
                    </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
   
      