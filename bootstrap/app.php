<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HandleLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind Render's HTTPS proxy: honor X-Forwarded-Proto/Host so Laravel
        // generates HTTPS asset/font/script URLs instead of mixed-content HTTP.
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state', 'locale']);

        $middleware->web(append: [
            // لازم يجي قبل بقية الطبقات: يضبط لغة التطبيق حتى ترجع رسائل
            // __() بنفس لغة الواجهة بدل الإنجليزية دايماً.
            HandleLocale::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // بدون هذا، خطأ 500 أثناء تنقّل Inertia يرجّع صفحة HTML خام
        // فتطلع شاشة بيضاء بدل رسالة مفهومة.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if (app()->environment('local') || ! $request->header('X-Inertia')) {
                return $response;
            }

            if (in_array($response->getStatusCode(), [500, 503, 404, 403, 429], true)) {
                return Inertia::render('Error', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })->create();
