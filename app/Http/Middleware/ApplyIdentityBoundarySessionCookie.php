<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Symfony\Component\HttpFoundation\Response;

class ApplyIdentityBoundarySessionCookie
{
    public function __construct(private readonly SessionManager $sessionManager) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $boundary = $request->attributes->get('identity_boundary', 'public');
        $cookie = (string) config("session.identity_boundary.cookies.{$boundary}", config('session.cookie'));

        config(['session.cookie' => $cookie]);

        if (app()->resolved('session')) {
            $this->sessionManager->driver()->setName($cookie);
        }

        if (app()->resolved('session.store')) {
            app('session.store')->setName($cookie);
        }

        return $next($request);
    }
}
