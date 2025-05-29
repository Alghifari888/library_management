<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole; // Tambahkan ini

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware global atau grup biasanya didaftarkan di sini
        // Contoh: $middleware->web(append: [ MyCustomMiddleware::class ]);

        // Daftarkan alias untuk middleware role kita
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        // Laravel Breeze mungkin telah menambahkan beberapa konfigurasi middleware di sini,
        // seperti `redirectGuestsTo` atau `redirectUsersTo`.
        // Pastikan alias role ditambahkan dengan benar.
        // Untuk Laravel 11+, konfigurasi default Breeze ada di sini.
        // Misalnya:
        // $middleware->redirectGuestsTo(fn () => route('login'));
        // $middleware->redirectUsersTo('/dashboard'); // Sesuaikan dengan kebutuhan
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();