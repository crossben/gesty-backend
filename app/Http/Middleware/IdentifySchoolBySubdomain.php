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
        // 0. Skip for Super Admin global routes
        if ($request->is('api/super-admin/*')) {
            return $next($request);
        }

        // 1. Check for explicit header (Path-based or Header-based multi-tenancy)
        $schoolSlug = $request->header('X-School-Slug');
        
        if (!$schoolSlug) {
            // 2. Fallback to subdomain identification
            $host = $request->getHost();
            $baseHost = config('app.url'); // e.g. "gesty.com" or "localhost"
            $baseHost = preg_replace('/^https?:\/\//', '', $baseHost);
            $baseHost = rtrim($baseHost, '/');

            if ($host !== $baseHost) {
                $subdomain = explode('.', $host)[0];
                if (!in_array($subdomain, ['app', 'api', 'www', 'admin'])) {
                    $schoolSlug = $subdomain;
                }
            }
        }

        if ($schoolSlug) {
            $school = School::where('slug', $schoolSlug)->first();

            if (!$school) {
                abort(404, "School not found.");
            }

            if (!$school->is_active) {
                abort(403, "School is locked. Please contact administration.");
            }

            $this->tenantManager->setSchool($school);
        }

        return $next($request);
    }
}
