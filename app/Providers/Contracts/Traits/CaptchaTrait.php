<?php

namespace App\B2c\Repositories\Contracts\Traits;

use ReCaptcha\ReCaptcha;
use Illuminate\Http\Request;
use ReCaptcha\RequestMethod\CurlPost;

trait CaptchaTrait
{

    public function captchaCheck(Request $request)
    {
        if(config('captcha.re_captcha_req')==true){
            $response = $request->get('g-recaptcha-response');
            $remoteip = $request->server('REMOTE_ADDR');
            $secret   = config('captcha.re_cap_secret');
            $recaptcha = new ReCaptcha($secret, new CurlPost());
            $resp      = $recaptcha->verify($response, $remoteip);            
            if ($resp->isSuccess()) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
