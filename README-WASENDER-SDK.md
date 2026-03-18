# WasenderAPI Laravel SDK (scaffold)

Pequeño scaffold del SDK de Wasender para integrarlo en este proyecto Laravel.

Instalación manual en este repositorio (scaffold):

- Registra el `ServiceProvider` en `config/app.php` providers:

  App\Services\WasenderApi\WasenderServiceProvider::class,

- (Opcional) Registra un alias para la fachada `WasenderApi` en el array `aliases` de `config/app.php`:

  'WasenderApi' => App\Facades\WasenderApi::class,

- Publica el config si quieres editarlo:

```
php artisan vendor:publish --tag=wasenderapi-config
```

Variables de entorno sugeridas en `.env`:

```
WASENDERAPI_API_KEY=your_api_key_here
WASENDERAPI_PERSONAL_ACCESS_TOKEN=your_personal_token_here
WASENDERAPI_WEBHOOK_SECRET=your_webhook_secret_here
```

Rutas:
- El archivo de rutas cargado por el provider es `routes/wasender.php` y expone `/wasender/webhook`.

Uso rápido (ejemplo):

```php
use App\Facades\WasenderApi;

WasenderApi::sendText('1234567890', 'Hola desde Laravel!');
```

Lo añadido por este scaffold:
- `app/Services/WasenderApi/WasenderClient.php` (cliente básico y retry)
- DTOs básicos en `app/Services/WasenderApi/DTO`
- `WasenderServiceProvider` para binding y carga de rutas
- `app/Facades/WasenderApi.php` (facade)
- `app/Http/Controllers/WasenderWebhookController.php` (verificación y dispatch de evento)
- `app/Events/MessagesUpserted.php`
- `config/wasenderapi.php` y `routes/wasender.php`

Siguientes pasos recomendados:
- Extender `WasenderClient` con el resto de métodos (sendImage, contacts, sessions, groups, etc.).
- Añadir pruebas con Pest y Testbench.
- Registrar los eventos adicionales y documentación de cada DTO.
