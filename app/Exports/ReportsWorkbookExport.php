<?php

namespace App\Exports;

use App\Exports\Sheets\CreditCustomersSheetExport;
use App\Exports\Sheets\MonthlySummarySheetExport;
use App\Exports\Sheets\OverviewSheetExport;
use App\Exports\Sheets\SalesSheetExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsWorkbookExport implements WithMultipleSheets
{
    public function __construct(
        protected Carbon $from,
        protected Carbon $to
    ) {
    }

    public function sheets(): array
    {
        return [
            new OverviewSheetExport($this->from, $this->to),
            new SalesSheetExport($this->from, $this->to),
            new CreditCustomersSheetExport(),
            new MonthlySummarySheetExport($this->from, $this->to),
        ];
    }
}
