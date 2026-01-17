<!-- Copilot instructions para agentes AI: resumen operativo del repo -->
# Instrucciones rápidas para agentes AI (proyecto: promarket-dashboard)

## 1) Panorama general y arquitectura
- **Stack**: Laravel 8 con Blade y `laravel/ui`, y assets administrados por `laravel-mix`/`npm` (vea `package.json` y `webpack.mix.js`).
- **Routing principal**: `routes/web.php` centraliza los grupos `dashboard`, `app`, `ui`, `component`, `page-layouts`, `form`, y en especial el prefijo `panel` para los módulos de negocio (tasa de cambio, productos, impuestos, cupones, etc.). Cada ruta del panel sigue la convención REST (`index`, `create`, `store`, `edit`, `update`, `destroy`, `status`).
- **Controladores y vistas**: los controladores de módulo viven en `app/Http/Controllers` (p.ej. `ExchangeRateController`, `ProductController`, `TaxController`, `CouponController`) y entregan datos a las vistas bajo `resources/views/panel/<modulo>`.
- **Menús compartidos**: `app/Providers/MenuServiceProvider.php` decodifica `resources/data/menu-data/{vertical,horizontal}Menu.json` y comparte `menuData` con cada vista. Cualquier nueva entrada del panel debe registrarse en esos JSON o los menús no reflejarán el cambio.

## 2) Flujos de desarrollo y comandos frecuentes
- **Setup**: `composer install`, copiar `.env.example` a `.env`, y ejecutar `php artisan key:generate`. El proyecto se prueba comúnmente sobre Laragon en Windows, pero `php artisan serve` funciona también.
- **Assets**: `npm install` seguido de `npm run dev`, `npm run production`, `npm run watch`, o `npm run hot` según convenga. Todos los scripts están definidos en `package.json` y usan `webpack.mix.js` para compilar SCSS/JS y copiar assets.
- **Pruebas**: se ejecutan con `vendor/bin/phpunit` o `./vendor/bin/phpunit`. No hay script npm para phpunit.

- **Panel UI**: toda nueva funcionalidad visual del panel debe tener su ruta en `routes/web.php`, su acción en el controlador correspondiente y vistas en `resources/views/panel/<nombre>`. Cada módulo centralizado en `panel/` sigue la convención `list`/`form`/`view` (por ejemplo `resources/views/panel/products/index.blade.php`). Las plantillas reutilizan `menuData` (desde `MenuServiceProvider`) y los assets compilados en `public/js/core` y `public/css`.
- **Listados y detalles**: use siempre el componente de `datatable` del panel para mostrar tablas (ya lo usan `resources/views/panel/exchange_rates/index.blade.php` o `resources/views/panel/products/index.blade.php`), tanto para la vista de lista como para los detalles/ediciones. Evite crear tablas sin `datatable`, y reutilice la configuración existente (`js/scripts/datatables/`).
- **Ejemplo de módulo**: los nuevos módulos como `panel/coupons` o `panel/taxes` siguen este patrón: su controlador vive en `app/Http/Controllers`, los formularios y listados están en `resources/views/panel/coupons` o `resources/views/panel/taxes`, y cada tabla respeta el mismo `datatable` compartido.
- **Locales**: el par `resources/lang/es/locale.php` y `resources/lang/en/locale.php` contiene cadenas traducidas; use `lang/{locale}` (controlado por `LanguageController::swap`) para cambiar idioma y mantenga ambos archivos sincronizados.
- **Menu data**: `resources/data/menu-data/verticalMenu.json` (y el horizontal) definen qué entradas aparecen en la barra lateral. Modifíquelos cuidadosamente y valide el JSON, ya que el `boot()` del proveedor falla si no puede parsearlo.
- **Copia de assets estáticos**: `resources/images` y `resources/data` se copian a `public/images` y `public/data` mediante `mix.copyDirectory` (ver `webpack.mix.js`). Evite agregar activos directamente en `public/` si se pueden generar desde `resources/`.
- **Carpeta public/img**: contiene imágenes de productos y ya está en `.gitignore`; no incluya esos archivos en los commits.

## 4) Compilación y dependencias clave
- **Mix y SCSS**: `webpack.mix.js` usa la función auxiliar `mixAssetsDir` para procesar SCSS en `resources/sass/base/*`, JS en `resources/js/scripts`, y copiar `vendors`, `fonts`, `images` y `data`. Añadir nuevos SCSS/JS dentro de esas carpetas garantiza que Mix los procese automáticamente.
- **RTL**: al fijar `MIX_CONTENT_DIRECTION=rtl`, el hook `mix.then()` ejecuta `rtlcss` en `public/css` para generar estilos espejo.
- **Dependencias**: el frontend usa `bootstrap@4.6.0`, `jquery`, `axios`, `rtlcss`, `sass`, `laravel-mix@6`, `webpack@5` y loaders como `sass-loader` y `resolve-url-loader` (vea `package.json`).

## 5) Qué verificar antes de subir cambios
- Para nuevas vistas: asegúrese de añadir rutas en `routes/web.php`, actualizar el controlador y mantener la consistencia de los nombres `index`, `form`, `create`, `edit`, etc., que ya existen bajo `panel`.
- Si agrega un módulo nuevo, revise que `app/Http/Controllers` tenga la nueva clase, que sus vistas vivan en `resources/views/panel/<modulo>` y que el menú lateral incluya la entrada en `resources/data/menu-data/verticalMenu.json`.
- Cambios en locales o menú necesitan el cache de vistas limpio (`php artisan view:clear`) y quizá recompilar (`npm run dev`) para que Blade detecte las nuevas cadenas.
- Antes de confirmar, vuelve a compilar assets (`npm run dev` o `npm run production`) y asegúrate de que los JSON modificados siguen siendo válidos; un error de sintaxis rompe la carga de `menuData`.

## Reglas obligatorias para agentes AI
- **Listados:** Siempre use el componente `datatable` del panel para todas las listas. No cree tablas HTML básicas para listados de datos; reutilice la configuración y estilos existentes en `js/scripts/datatables/`.
- **Traducciones:** Todas las etiquetas visibles en las vistas deben estar traducidas. Use claves en `resources/lang/en/locale.php` y `resources/lang/es/locale.php` (o archivos específicos por módulo) y `__()` en Blade. No dejar cadenas literales en las vistas.
- **Imágenes de referencia:** Si el usuario adjunta una captura o maqueta, tómatela como referencia y haz la vista lo más parecida posible (estructura, orden de columnas, íconos y espaciado). No improvises diseños muy distintos.
- **Imágenes de referencia:** Si el usuario adjunta una captura o maqueta, tómatela como referencia y haz la vista lo más parecida posible (estructura, orden de columnas, íconos y espaciado). No improvises diseños muy distintos.
- **Iconos:** Use un estándar único de iconos en las listas (por ejemplo `feather`), mantenga el mismo conjunto, orden y tamaños para las acciones (ver, editar, imprimir, eliminar). Use `data-feather` y clases consistentes para estilos; evite mezclar librerías de iconos dentro de la misma vista.
- **Ubicación de las vistas:** Todas las vistas de la UI administrativa deben vivir en `resources/views/panel/<modulo>`. Crea una carpeta por módulo y coloca `index.blade.php`, `form.blade.php`, `_details.blade.php`, etc., siguiendo la convención del proyecto.
- **Controladores:** Crea los controladores en la raíz `app/Http/Controllers` (no en `app/Http/Controllers/Admin`). Si hay referencias antiguas a `Admin\`, deja un _stub_ con comentario indicando la deprecación y apunta las rutas al controlador raíz.
- **Entradas de menú:** Cuando agregues un módulo nuevo, registra su entrada en `resources/data/menu-data/verticalMenu.json` (y horizontal si aplica) para que aparezca en la barra lateral. Valida el JSON antes de compilar.
- **Comprobaciones rápidas antes de entregar:** ejecutar `php artisan view:clear`, `php artisan config:clear` y `npm run dev` localmente para confirmar que Blade e idiomas cargan correctamente.

## 6) Preguntas abiertas para el autor
- ¿Se espera que los builds de assets se ejecuten automáticamente en CI con `npm ci` o solo se hagan localmente?
- ¿Hay convenciones adicionales para las vistas dentro de `resources/views/panel`, como usar `index.blade.php` frente a `form.blade.php`?
- ¿Los nuevos módulos deben reutilizar un layout Blade ya existente o prefieres crear vistas específicas?

Si algo aquí queda poco claro, dime qué sección quieres que amplíe (comandos de Windows, ejemplos de vistas, o mapeo controlador→vista).
