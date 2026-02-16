<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SaleController extends Controller
{
    public function export(): BinaryFileResponse
    {
        $dateFrom = request()->query('date_from');
        $dateTo = request()->query('date_to');
        $filename = 'vendas_' . ($dateFrom ?: 'inicio') . '_' . ($dateTo ?: 'fim') . '.xlsx';

        return Excel::download(
            new SalesExport($dateFrom ?: null, $dateTo ?: null),
            $filename
        );
    }
}
