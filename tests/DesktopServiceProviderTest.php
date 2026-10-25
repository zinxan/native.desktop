<?php

declare(strict_types=1);

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZXN\ZXGuard\Core\Laravel\ZXGuard;
use ZXN\ZXGuard\Desktop\DesktopServiceProvider;

beforeEach(function (): void {
    Http::preventStrayRequests();
    Http::fake(['zxguard.test/*' => Http::response(status: 202)]);

    config([
        'zxguard.key' => 'zxg_desktop',
        'zxguard.url' => 'https://zxguard.test',
        'zxguard.outbox.path' => temporaryOutbox(),
        'nativephp.version' => '1.4.0',
        'nativephp.app_id' => 'com.zxn.notes',
    ]);
});

it('reports exceptions after the response with desktop context', function (): void {
    $this->app->register(DesktopServiceProvider::class);

    Log::info('Opened the main window');
    report(new RuntimeException('Could not save the note.'));

    expect(ZXGuard::pending())->toBe(1);
    Http::assertNothingSent();

    $this->app->terminate();

    Http::assertSent(fn (Request $request): bool => $request->header('X-ZXGuard-Key') === ['zxg_desktop']
        && $request['platform'] === 'desktop'
        && $request['release'] === '1.4.0'
        && $request['context']['app']['id'] === 'com.zxn.notes'
        && $request['context']['runtime']['php'] === PHP_VERSION
        && $request['exception']['value'] === 'Could not save the note.'
        && collect($request['breadcrumbs'])->contains('message', 'Opened the main window')
        && $request['sdk']['name'] === 'zxn/zx-desktop');
});

it('does not hook into the app without a key', function (): void {
    config(['zxguard.key' => null]);
    $this->app->register(DesktopServiceProvider::class);

    report(new RuntimeException('Boom'));

    expect(ZXGuard::pending())->toBe(0);
});

it('sends a test error from the command line', function (): void {
    $this->app->register(DesktopServiceProvider::class);

    $this->artisan('zxguard:test')
        ->expectsOutputToContain('The test error was sent')
        ->assertSuccessful();

    Http::assertSentCount(1);
});

it('explains how to enable the sdk when the key is missing', function (): void {
    config(['zxguard.key' => null]);
    $this->app->register(DesktopServiceProvider::class);

    $this->artisan('zxguard:test')
        ->expectsOutputToContain('Set ZXGUARD_KEY')
        ->assertFailed();
});

it('reports exceptions when a wrapper such as collision replaces the exception handler', function (): void {
    $inner = $this->app->make(ExceptionHandler::class);

    $this->app->singleton(ExceptionHandler::class, fn (): ExceptionHandler => new class($inner) implements ExceptionHandler
    {
        public function __construct(private ExceptionHandler $inner) {}

        public function report(Throwable $e): void
        {
            $this->inner->report($e);
        }

        public function shouldReport(Throwable $e): bool
        {
            return $this->inner->shouldReport($e);
        }

        public function render($request, Throwable $e)
        {
            return $this->inner->render($request, $e);
        }

        public function renderForConsole($output, Throwable $e): void
        {
            $this->inner->renderForConsole($output, $e);
        }

        public function reportable(callable $callback): mixed
        {
            return $this->inner->reportable($callback);
        }
    });

    $this->app->register(DesktopServiceProvider::class);

    $this->app->make(ExceptionHandler::class)->report(new RuntimeException('Thrown in a console command.'));

    expect(ZXGuard::pending())->toBe(1);
});
