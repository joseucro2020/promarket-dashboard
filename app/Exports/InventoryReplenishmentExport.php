<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InventoryReplenishmentExport implements FromView
{
    private $replenishments;
    private $today;

    public function __construct($replenishments, string $today)
    {
        $this->replenishments = $replenishments;
        $this->today = $today;
    }

    public function view(): View
    {
        return view('panel.inventory.export_excel', [
            'replenishments' => $this->replenishments,
            'today' => $this->today,
        ]);
    }
}
