<!-- Copilot instructions para agentes AI: resumen operativo del repo -->
# Instrucciones rápidas para agentes AI (proyecto: promarket-dashboard)

Este proyecto es una aplicación Laravel (v8) con frontend compilado por `laravel-mix`.
Las instrucciones aquí se enfocan en lo que un agente necesita saber para ser productivo rápidamente.

1) Gran panorama
- **Stack**: Laravel 8 (PHP ^7.3|^8.0), Blade views, assets administrados con `laravel-mix` + `npm`.
- **Estructura clave**: `routes/web.php` define las rutas principales; controladores en `app/Http/Controllers`; vistas en `resources/views`.
- **Autoload**: `composer.json` registra `app/` y además carga `app/Helpers/helpers.php` vía `autoload.files`.

2) Flujos de desarrollo / comandos frecuentes
- **Instalación PHP**: `composer install`.
- **Config .env (Windows)**: `copy .env.example .env` y `php artisan key:generate`.
- **Assets**: `npm install` luego `npm run dev` (desarrollo) o `npm run production` (build optimizado).
- **Servidor**: en desarrollo usar Laragon o `php artisan serve` si se prefiere.
- **Tests**: `vendor/bin/phpunit` o `./vendor/bin/phpunit` (no hay script npm para tests).

3) Patrones y convenciones específicas del proyecto
- **Compilación de assets por convención**: `webpack.mix.js` usa la función `mixAssetsDir('sass/base/plugins/**/!(_)*.scss', ...)`.
  - Añadir SCSS en `resources/sass/base/...` o `resources/assets/scss/` permite que Mix lo incluya automáticamente.
  - `resources/data` y `resources/images` se copian a `public/data` y `public/images` por Mix.
- **RTL build**: si `MIX_CONTENT_DIRECTION` en `.env` está definido a `rtl`, el proceso ejecuta `rtlcss` sobre `public/css/`.
- **Menus**: `app/Providers/MenuServiceProvider.php` lee `resources/data/menu-data/verticalMenu.json` y `horizontalMenu.json` y comparte `menuData` a todas las vistas. Evita duplicar esa lógica; actualiza esos JSON para cambiar menús.
- **Rutas y vistas**: las acciones de controladores devuelven vistas con nombres consistentes — busca en `routes/web.php` para mapear paths a controladores (por ejemplo la ruta `'/'` llama `DashboardController::dashboardEcommerce`).
- **Localización**: existe la ruta `lang/{locale}` manejada por `LanguageController::swap` — usa este punto para cambios de idioma.

4) Integraciones externas y dependencias notables
- **Frontend**: `bootstrap@4.6.0`, `jquery`, `axios`, `rtlcss`.
- **Laravel**: `laravel/ui` está presente (flujo de autenticación vía `Auth::routes()`).
- **Datos estáticos**: `resources/data` contiene JSON usados por la UI (menús, listados de ejemplo, etc.).

5) Qué buscar cuando cambias código
- Si tocas vistas/plantillas: confirmar que los assets CSS/JS correspondientes estén en `resources/sass` o `resources/js` para que Mix los procese.
- Si añades una nueva sección de UI, registra rutas en `routes/web.php` y crea el controlador correspondiente en `app/Http/Controllers` (siguiendo la convención existente).
- Evita tocar `MenuServiceProvider::boot()` sin validar que `resources/data/menu-data/*.json` siga siendo válido JSON; cualquier error rompe el compartido en vistas.

6) Ejemplos concretos extraídos del repo
- Ruta principal: `routes/web.php` → `Route::get('/', [DashboardController::class,'dashboardEcommerce'])->name('dashboard-ecommerce');`
- Menú compartido: `app/Providers/MenuServiceProvider.php` lee `resources/data/menu-data/verticalMenu.json` y hace `\\View::share('menuData', [...])`.
- Mix: `webpack.mix.js` compila `resources/js/core/app.js` y `resources/sass/core.scss` y copia `resources/data` a `public/data`.

7) Recomendaciones para PRs y revisiones automatizadas
- Ejecuta `composer install` y `npm ci && npm run dev` (o `npm run production`) en el pipeline antes de probar visualmente los cambios.
- Para cambios en CSS/JS, asegúrate de que los archivos estén en las rutas que `webpack.mix.js` inspecciona (usa `mixAssetsDir` patterns).
- Cuando modifiques JSON bajo `resources/data`, añade una verificación rápida de sintaxis JSON en los tests / CI.

8) Preguntas abiertas que un agente puede hacer al autor
- ¿Se prefiere que los builds de assets se ejecuten en CI (sí/no)?
- ¿Hay convenciones de nombres de vistas (por ejemplo `dashboard-ecommerce` -> `resources/views/dashboard/ecommerce.blade.php`)?

Si algo aquí queda poco claro, dime qué sección quieres que amplíe (comandos de Windows, ejemplos de vistas, o mapeo controlador→vista).
