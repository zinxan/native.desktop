# zinxan/native-desktop

[Contributing](https://github.com/zinxan/native.core/blob/main/CONTRIBUTING.md)

Error tracking for NativePHP desktop apps. Errors go to [NativeWatch](https://native.watch).

```bash
composer require zinxan/native-desktop
```

Add the key of your NativeWatch project to `.env`:

```env
NATIVEWATCH_KEY=nw_...
NATIVEWATCH_URL=https://native.watch
```

Send a test error:

```bash
php artisan nativewatch:test
```

Reportable exceptions enter a bounded on-disk outbox. NativeWatch normally sends
them after the response and retains failed network deliveries for a later retry.
Capture and disk work have a cost; an unwritable outbox can cause an immediate
send attempt. Read the [offline delivery guide](https://native.watch/docs/offline-delivery)
for retry, capacity and permanent rejection behavior.

Before shipping, review [privacy and diagnostic consent](https://native.watch/docs/privacy-and-consent).
Use the [desktop installation guide](https://native.watch/docs/install-desktop)
to verify a real stored event from your packaged app.

Report something yourself:

```php
use ZXN\NativeWatch\Core\Laravel\NativeWatch;
use ZXN\NativeWatch\Core\Level;

NativeWatch::addBreadcrumb('sync', 'Started the sync');
NativeWatch::captureMessage('The disk is almost full', Level::Warning);
NativeWatch::captureException($exception);
```

Publish the config to change sampling, scrubbing or breadcrumbs:

```bash
php artisan vendor:publish --tag=nativewatch-config
```
