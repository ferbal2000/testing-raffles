<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyIdentityBoundary
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $boundary = $this->resolveBoundary($request);

        $request->attributes->set('identity_boundary', $boundary);

        return $next($request);
    }

    private function resolveBoundary(Request $request): string
    {
        $host = $request->getHost();
        $adminHost = $this->hostFromConfig('app.admin_url');

        if ($adminHost !== null && $host === $adminHost) {
            return 'admin';
        }

        return 'public';
    }

    private function hostFromConfig(string $configKey): ?string
    {
        $host = parse_url((string) config($configKey), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : null;
    }
}
