<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifySchoolBySubdomain
{
    public function __construct(protected TenantManager $tenantManager)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $baseHost = config('app.url'); // e.g. "gesty.com" or "localhost"
        
        // Remove protocol and trailing slashes if any
        $baseHost = preg_replace('/^https?:\/\//', '', $baseHost);
        $baseHost = rtrim($baseHost, '/');

        // If the host is exactly the base host, it's the main landing/login
        if ($host === $baseHost) {
            return $next($request);
        }

        // Extract subdomain
        $subdomain = explode('.', $host)[0];
        
        // List of reserved subdomains
        if (in_array($subdomain, ['app', 'api', 'www', 'admin'])) {
            return $next($request);
        }

        $school = School::where('slug', $subdomain)->first();

        if (!$school) {
            abort(404, "School not found.");
        }

        $this->tenantManager->setSchool($school);

        return $next($request);
    }
}
