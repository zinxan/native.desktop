<?php

declare(strict_types=1);

namespace ZXN\NativeWatch\Desktop;

use Composer\InstalledVersions;
use Illuminate\Contracts\Config\Repository;
use ZXN\NativeWatch\Core\Contracts\ContextProvider;

final readonly class DesktopContext implements ContextProvider
{
    public function __construct(
        private Repository $config,
        private string $laravelVersion,
        private bool $sendDefaultPii = false,
    ) {}

    /**
     * Get the platform that the app runs on.
     */
    public function platform(): string
    {
        return 'desktop';
    }

    /**
     * Get the version of the NativePHP app.
     */
    public function release(): ?string
    {
        $version = $this->config->get('nativephp.version');

        return is_string($version) && $version !== '' ? $version : null;
    }

    /**
     * Get the context that describes the machine, the app and the runtime.
     *
     * @return array<string, array<string, mixed>>
     */
    public function context(): array
    {
        return array_filter([
            'os' => [
                'name' => $this->osName(),
                'kernel' => php_uname('r'),
                'arch' => php_uname('m'),
            ],
            'app' => array_filter([
                'id' => $this->config->get('nativephp.app_id'),
                'version' => $this->release(),
                'native' => (bool) $this->config->get('nativephp-internal.running', false),
            ], fn (mixed $value): bool => $value !== null),
            'runtime' => array_filter([
                'php' => PHP_VERSION,
                'laravel' => $this->laravelVersion,
                'nativephp' => $this->packageVersion('nativephp/desktop'),
            ]),
            'device' => $this->sendDefaultPii ? ['hostname' => (string) gethostname()] : [],
        ]);
    }

    private function osName(): string
    {
        return match (PHP_OS_FAMILY) {
            'Darwin' => 'macOS',
            default => PHP_OS_FAMILY,
        };
    }

    private function packageVersion(string $package): ?string
    {
        return class_exists(InstalledVersions::class) && InstalledVersions::isInstalled($package)
            ? InstalledVersions::getPrettyVersion($package)
            : null;
    }
}
