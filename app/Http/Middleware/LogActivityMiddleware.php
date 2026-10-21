<?php

// app/Http/Middleware/LogActivityMiddleware.php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Support\Facades\Auth;

class LogActivityMiddleware
{
    protected $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Log only POST, PUT, PATCH, DELETE requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $user = Auth::user();
            $userId = $user ? $user->id : 'guest';
            $userName = $user ? $user->name : 'Guest';

            // Skip logging for AJAX requests (optional)
            if ($request->ajax() || $request->wantsJson()) {
                return $response;
            }

            // Skip logging for specific routes
            $skipRoutes = [
                'admin/login',
                'admin/logout',
                'api/*',
                '_debugbar/*',
                'admin/moderation/*',
                'admin/exam-moderation/*',
                'admin/results-moderation/*',
            ];

            foreach ($skipRoutes as $route) {
                if ($request->routeIs($route) || $request->is($route)) {
                    return $response;
                }
            }

            $this->activityLogger->log(
                "{$request->method()} request to {$request->path()}",
                null,
                [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'input' => $request->except(['password', 'password_confirmation']),
                    'user_id' => $userId,
                    'user_name' => $userName,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'request'
            );
        }

        return $response;
    }
}
