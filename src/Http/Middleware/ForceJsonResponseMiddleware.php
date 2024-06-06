<?php

namespace Plytas\Discord\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponseMiddleware
{
    /**
     * @template TNext
     *
     * @param  Closure(Request): TNext  $next
     * @return TNext
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
