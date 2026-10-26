<?php

namespace App\Http\Middleware;

use App\Services\StorageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStorageWritable
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            if ($request->hasFile('fotos') || $request->hasFile('cover_photo') || $request->hasFile('bukti_pembayaran')) {
                StorageService::assertWritable();
            }
        }

        return $next($request);
    }
}
