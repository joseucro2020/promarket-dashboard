<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ClientExport implements FromView, WithTitle
{
    private $clients;
    private $today;

    public function __construct($clients, string $today)
    {
        $this->clients = $clients;
        $this->today = $today;
    }

    public function view(): View
    {
        return view('panel.clients.export_excel', [
            'clients' => $this->clients,
            'today' => $this->today,
        ]);
    }

    /**
     * Return a safe sheet title (PhpSpreadsheet limits: max 31 chars, forbids []:*?/\\)
     */
    public function title(): string
    {
        // Use a short, ASCII-only safe title to avoid PhpSpreadsheet validation issues
        return 'Clientes';
    }
}
