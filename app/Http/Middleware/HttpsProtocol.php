<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
class HttpsProtocol {

    public function handle($request, Closure $next)
    {
            if (!$request->secure()) {
                dd($request->getRequestUri());
               return redirect()->secure($request->getRequestUri());
            }

            return $next($request); 
    }
}
?>