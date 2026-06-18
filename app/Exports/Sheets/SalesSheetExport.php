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

class SalesSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    public function __construct(
        protected Carbon $from,
        protected Carbon $to
    ) {
    }

    public function collection()
    {
        return Sale::with(['customer', 'user'])
            ->whereBetween('sale_date', [$this->from, $this->to])
            ->orderBy('sale_date')
            ->get();
    }

    public function headings(): array
    {
        return ['Invoice', 'Date', 'Customer', 'Payment Type', 'Total', 'Paid', 'Due', 'Status', 'Created By'];
    }

    public function map($sale): array
    {
        return [
            $sale->invoice_no,
            $sale->sale_date?->format('Y-m-d'),
            $sale->customer?->name ?? 'Walk-in',
            strtoupper($sale->payment_type),
            (float) $sale->total_amount,
            (float) $sale->paid_amount,
            (float) $sale->due_amount,
            strtoupper($sale->payment_status),
            $sale->user?->name,
        ];
    }

    public function title(): string
    {
        return 'Sales';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A'],
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_DATE_YYYYMMDD,
            'E' => NumberFormat::FORMAT_NUMBER_00,
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'G' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:'.$highestColumn.'1');
                $sheet->getStyle('A1:'.$highestColumn.$highestRow)
                    ->getAlignment()
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            },
        ];
    }
}
