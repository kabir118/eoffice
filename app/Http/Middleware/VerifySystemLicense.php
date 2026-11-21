<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenseService;
use Symfony\Component\HttpFoundation\Response;

class VerifySystemLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Execute License & Domain Validation Check
        if (!LicenseService::validate()) {
            $currentHost = LicenseService::getCurrentHost();
            return response()->view('errors.lockout', ['domain' => $currentHost], 403);
        }

        return $next($request);
    }
}
