<?php

declare(strict_types=1);

use ZXN\NativeWatch\Desktop\Tests\TestCase;

pest()->extend(TestCase::class)->in(__DIR__);

function temporaryOutbox(): string
{
    $path = sys_get_temp_dir().'/nativewatch-tests/'.bin2hex(random_bytes(6));
    mkdir($path, 0755, true);

    return $path;
}
