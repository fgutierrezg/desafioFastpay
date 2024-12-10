<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Merchant;

class AuthenticateMerchant
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

        try {

            $merchant  = JWTAuth::parseToken()->authenticate();//->get('sub');

        } catch (\Exception $e) {
            // Si el token no es válido, regresa un error 401
            return response()->json(['message' => 'No autorizado'], 401);
        }

        if ($request->route('id') != $merchant->id) {
            return response()->json(['message' => 'Acceso denegado al recurso'], 403);
        }

        return $next($request);
    }
}
