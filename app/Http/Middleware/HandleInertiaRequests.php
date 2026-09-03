<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Share the layout direction with the root Blade template.
     *
     * The `locale` Inertia prop is not visible to the root view, so the
     * html tag needs its own View::share (same pattern as HandleAppearance).
     * Arabic/RTL is the default until the visitor explicitly picks English.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isEnglish = $request->cookie('locale', 'ar') === 'en';

        View::share('locale', $isEnglish ? 'ltr' : 'rtl');
        View::share('htmlLang', $isEnglish ? 'en' : 'ar');

        return parent::handle($request, $next);
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'locale' => $request->cookie('locale', 'ar') === 'en' ? 'ltr' : 'rtl',
            'flash' => [
                'toast' => $request->session()->get('toast'),
            ],
        ];
    }
}
