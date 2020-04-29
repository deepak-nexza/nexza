<?php

namespace Nexza\Core\Support\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Nexza\Core\Support\UrlParamProtector;

class UrlParamRevealerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guest() === false) {
            //Code added for tracking urls without signature
            $signature = ($request->query('__buffer') ? : false);
            if ($request->getQueryString() !== null && $signature === false) {
                if (App::environment('security')) {
                    abort(400);
                } else {
                    config(['mail.driver' => 'mail']);
                    if (App::environment('uat') || App::environment('preprod') || App::environment('production') || App::environment('live')) {
                    \Mail::raw(
                        "Url don't have signature " . $request->capture()->fullUrl(),
                        function ($message) {
                            $message->from(config('errorgroup.error_notification_email'), config('errorgroup.error_notification_from'));
                            $message->subject('Signature Missing - HSBC');
                            $message->to(config('errorgroup.error_notification_group'));
                        }
                    );
                  }
                }
            }

            App::make(UrlParamProtector::class)->reveal($request);
        }

        return $next($request);
    }
}
