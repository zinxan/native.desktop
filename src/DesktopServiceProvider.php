<?php

declare(strict_types=1);

namespace ZXN\NativeWatch\Desktop;

use Illuminate\Contracts\Foundation\Application;
use ZXN\NativeWatch\Core\Config;
use ZXN\NativeWatch\Core\Contracts\ContextProvider;
use ZXN\NativeWatch\Core\Laravel\NativeWatchServiceProvider;

final class DesktopServiceProvider extends NativeWatchServiceProvider
{
    /**
     * Get the name of the SDK, as it shows on every event.
     */
    protected function sdkName(): string
    {
        return 'zinxan/native-desktop';
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
