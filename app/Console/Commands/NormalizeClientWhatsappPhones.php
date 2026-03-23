<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PhoneNormalizationService;
use Illuminate\Console\Command;

class NormalizeClientWhatsappPhones extends Command
{
    protected $signature = 'clients:normalize-whatsapp-phones
                            {--dry-run : Simula cambios sin persistir en BD}
                            {--overwrite-telefono : Reemplaza telefono con el valor normalizado}
                            {--chunk=500 : Tamaño de lote para procesamiento}';

    protected $description = 'Normaliza teléfonos de clientes al formato WhatsApp (58XXXXXXXXXX) y guarda en telefono_whatsapp';

    public function handle(PhoneNormalizationService $phoneService)
    {
        $dryRun = (bool) $this->option('dry-run');
        $overwriteTelefono = (bool) $this->option('overwrite-telefono');
        $chunkSize = (int) $this->option('chunk');

        if ($chunkSize <= 0) {
            $chunkSize = 500;
        }

        $this->info('Iniciando normalización de teléfonos de clientes...');
        $this->line('Modo: ' . ($dryRun ? 'DRY-RUN (sin guardar)' : 'EJECUCIÓN REAL'));

        $processed = 0;
        $updated = 0;
        $invalid = 0;
        $unchanged = 0;

        User::query()
            ->where('nivel', '1')
            ->where('pro_seller', User::IS_NOT_PRO)
            ->whereNotNull('telefono')
            ->where('telefono', '!=', '')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($users) use (
                $phoneService,
                $dryRun,
                $overwriteTelefono,
                &$processed,
                &$updated,
                &$invalid,
                &$unchanged
            ) {
                foreach ($users as $user) {
                    $processed++;

                    $normalized = $phoneService->normalizeWhatsappVe($user->telefono);

                    if ($normalized === null) {
                        $invalid++;
                        continue;
                    }

                    $shouldUpdateWhatsapp = $user->telefono_whatsapp !== $normalized;
                    $shouldUpdateTelefono = $overwriteTelefono && $user->telefono !== $normalized;

                    if (!$shouldUpdateWhatsapp && !$shouldUpdateTelefono) {
                        $unchanged++;
                        continue;
                    }

                    $updated++;

                    if (!$dryRun) {
                        if ($shouldUpdateWhatsapp) {
                            $user->telefono_whatsapp = $normalized;
                        }

                        if ($shouldUpdateTelefono) {
                            $user->telefono = $normalized;
                        }

                        $user->save();
                    }
                }
            });

        $this->newLine();
        $this->info('Proceso finalizado.');
        $this->line('Procesados: ' . $processed);
        $this->line('Actualizados: ' . $updated);
        $this->line('Sin cambios: ' . $unchanged);
        $this->line('Inválidos: ' . $invalid);

        return 0;
    }
}
