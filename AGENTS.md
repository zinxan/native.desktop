# zinxan/native-desktop

`native-desktop` reports the errors of NativePHP desktop apps (Electron today, SuperNative desktop when it ships). It holds two classes: `DesktopServiceProvider` hooks `native-core` into the app, and `DesktopContext` describes the machine, the app and the runtime. Everything else comes from `native-core`. Read `packages/native-core/AGENTS.md` first.

---

## 1. Rules

- Put a feature that mobile apps also need in `native-core`, not here.
- Read NativePHP only through config: `nativephp.version`, `nativephp.app_id` and `nativephp-internal.running`. Do not import a class of `nativephp/desktop`. The package is a suggestion, not a requirement, so the SDK also works before NativePHP is installed.
- Use `nativephp.version` as the release when `NATIVEWATCH_RELEASE` is empty.
- Send the hostname only when `send_default_pii` is `true`. It often holds the name of a person.
- Remember the runtime: NativePHP desktop serves each request with the PHP built-in server and runs queue workers in child processes. In-memory state lives for one request. The outbox on disk (`storage/nativewatch/outbox`, inside the app data directory of NativePHP) carries events across requests, crashes and restarts.

---

## 2. When SuperNative desktop ships

- Detect it in `DesktopContext` and add it to the `runtime` context.
- Add a hook in `DesktopServiceProvider::bootPlatform()` only when its lifecycle differs from a request and a queue job.
- Add native crash capture as a NativePHP plugin with its own manifest, the way `native-mobile` plans it.

---

## 3. Tests

- Put the tests in `packages/native-desktop/tests/`. They run in the root suite.
- Register the provider in the test after you set the config: `$this->app->register(DesktopServiceProvider::class)`.
- Check the full path with `report()`, then `$this->app->terminate()`, then `Http::assertSent()`.
- Try the SDK in a real app with `examples/desktop`.
