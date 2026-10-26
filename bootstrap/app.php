<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        
        // Pasang firewall terdepan di pipeline HTTP
        $middleware->prepend(\App\Http\Middleware\SecurityFirewallMiddleware::class);
        
        $middleware->validateCsrfTokens(except: [
            '/api/webhook/mutasi',
            'api/webhook/mutasi',
            'api/webhook/mutasi/*',
            '/webhook/whatsapp',
            'webhook/whatsapp',
            'webhook/whatsapp/*',
        ]);
        
        // Tambahkan alias di sini
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'storage.writable' => \App\Http\Middleware\EnsureStorageWritable::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ...
    })->create();
