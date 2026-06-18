<?php

namespace App\Exports\Sheets;

use App\Models\Customer;
use App\Models\Sale;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OverviewSheetExport implements FromArray, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(
        protected Carbon $from,
        protected Carbon $to
    ) {
    }

    public function array(): array
    {
        $salesQuery = Sale::whereBetween('sale_date', [$this->from, $this->to]);

        $totalSales = (float) (clone $salesQuery)->sum('total_amount');
        $totalPaid = (float) (clone $salesQuery)->sum('paid_amount');
        $totalPending = (float) (clone $salesQuery)->sum('due_amount');
        $totalCashSales = (float) (clone $salesQuery)->where('payment_type', 'cash')->sum('total_amount');
        $totalCreditSales = (float) (clone $salesQuery)->where('payment_type', 'credit')->sum('total_amount');
        $totalSaleInvoices = (int) (clone $salesQuery)->count();

        $customersInRange = Customer::whereHas('sales', function ($query): void {
            $query->whereBetween('sale_date', [$this->from, $this->to]);
        })->count();

        $creditCustomersInRange = Customer::whereHas('sales', function ($query): void {
            $query->where('payment_type', 'credit')->whereBetween('sale_date', [$this->from, $this->to]);
        })->count();

        return [
            ['Report Summary', ''],
            ['From Date', $this->from->format('Y-m-d')],
            ['To Date', $this->to->format('Y-m-d')],
            ['Generated At', now()->format('Y-m-d H:i:s')],
            ['', ''],
            ['Metric', 'Value'],
            ['Total Sales Amount', $totalSales],
            ['Total Paid Amount', $totalPaid],
            ['Total Pending Amount', $totalPending],
            ['Cash Sales Amount', $totalCashSales],
            ['Credit Sales Amount', $totalCreditSales],
            ['Total Sale Invoices', $totalSaleInvoices],
            ['Customers (in range)', $customersInRange],
            ['Credit Customers (in range)', $creditCustomersInRange],
        ];
    }

    public function title(): string
    {
        return 'Overview';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '111827'],
                ],
            ],
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:B1');
                $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1:B14')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('B7:B11')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            },
        ];
    }
}
