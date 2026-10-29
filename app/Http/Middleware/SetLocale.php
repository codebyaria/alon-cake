<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Indonesian is the default; anything under /en is English (CLAUDE.md > Aturan produk).
 */
class SetLocale
{
    public const SUPPORTED = ['id', 'en'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($request->segment(1) === 'en' ? 'en' : 'id');

        return $next($request);
    }
}
