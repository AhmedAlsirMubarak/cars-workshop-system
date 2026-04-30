<?php
// ============================================================
// app/Http/Controllers/LanguageController.php
// Sets the application locale via session
// ============================================================

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        $supported = ['en', 'ar'];

        if (!in_array($locale, $supported)) {
            abort(400, 'Unsupported locale.');
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return response()->json(['locale' => $locale]);
    }
}


// ============================================================
// app/Http/Middleware/SetLocale.php
// Reads locale from session on every request
// Add to web middleware group in bootstrap/app.php
// ============================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', config('app.locale', 'en'));

        if (in_array($locale, ['en', 'ar'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
