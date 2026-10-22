<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('adminMiddle')->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                // abort() alih-alih return response(): Livewire menjalankan ulang middleware ini
                // di request update dan hanya berhenti kalau middleware melempar exception atau redirect
                abort(401, 'Unauthorized.');
            } else {
                return redirect(url('admin/login'));
            }
        }
        return $next($request);
    }
}
