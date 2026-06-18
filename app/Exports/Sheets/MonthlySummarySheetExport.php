<?php

namespace App\Exports\Sheets;

use App\Models\Sale;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlySummarySheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    public function __construct(
        protected Carbon $from,
        protected Carbon $to
    ) {
    }

    public function collection()
    {
        return Sale::selectRaw('DATE_FORMAT(sale_date, "%Y-%m") as month')
            ->selectRaw('SUM(total_amount) as total_sales')
            ->selectRaw('SUM(paid_amount) as total_paid')
            ->selectRaw('SUM(due_amount) as total_due')
            ->whereBetween('sale_date', [$this->from, $this->to])
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function headings(): array
    {
        return ['Month', 'Total Sales', 'Total Paid', 'Total Due'];
    }

    public function map($row): array
    {
        return [
            $row->month,
            (float) $row->total_sales,
            (float) $row->total_paid,
            (float) $row->total_due,
        ];
    }

    public function title(): string
    {
        return 'Monthly Summary';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '065F46'],
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_00,
            'C' => NumberFormat::FORMAT_NUMBER_00,
            'D' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:'.$highestColumn.'1');
            },
        ];
    }
}
