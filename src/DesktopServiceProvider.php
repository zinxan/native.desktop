<?php

declare(strict_types=1);

namespace ZXN\ZXGuard\Desktop;

use Illuminate\Contracts\Foundation\Application;
use ZXN\ZXGuard\Core\Config;
use ZXN\ZXGuard\Core\Contracts\ContextProvider;
use ZXN\ZXGuard\Core\Laravel\ZXGuardServiceProvider;

final class DesktopServiceProvider extends ZXGuardServiceProvider
{
    /**
     * Get the name of the SDK, as it shows on every event.
     */
    protected function sdkName(): string
    {
        return 'zxn/zx-desktop';
    }

    /**
     * Get the context provider of the platform.
     */
    protected function contextProvider(Application $app): ContextProvider
    {
        return new DesktopContext(
            $app->make('config'),
            $app->version(),
            $app->make(Config::class)->sendDefaultPii,
        );
    }
}
