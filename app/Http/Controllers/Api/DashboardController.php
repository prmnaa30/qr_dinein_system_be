<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportSalesRequest;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Authenticated;

#[Group('Dashboard Management', 'APIs for managing dashboard data and reports in the system.')]
class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    #[Endpoint('Tampilkan Dashboard Admin', 'Menampilkan data dashboard admin untuk analisis dan statistik.')]
    #[Authenticated]
    #[Response(content: '{"message": "Data dashboard berhasil diambil.","data": {"total_orders": 100,"total_revenue": 5000000,"total_customers": 80,"recent_orders": []}}', status: 200)]
    public function index()
    {
        Gate::authorize('viewAdminDashboard', User::class);
        $data = $this->dashboardService->getAdminDashboardData();

        return response()->json([
            'message' => 'Data dashboard berhasil diambil.',
            'data' => $data
        ]);
    }

    #[Endpoint('Ekspor Laporan Penjualan', 'Mengekspor laporan penjualan dalam rentang tanggal tertentu.')]
    #[Authenticated]
    #[Response(content: '{"data": [{"order_id": 1,"date": "2023-01-01","customer_name": "John Doe","table_number": "A1","items_summary": "Nasi Goreng, Es Teh","total_amount": 50000}],"total_revenue": 500000}', status: 200)]
    public function exportSales(ExportSalesRequest $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $reportData = $this->dashboardService->getSalesReport($startDate, $endDate);

        if ($request->wantsJson() && !$request->has('export')) {
            return response()->json([
                'data' => $reportData,
                'total_revenue' => $reportData->sum('total_price')
            ]);
        }

        if ($request->get('export') == 'csv') {
            return $this->createCsvByDateRange($startDate, $endDate, $reportData);
        }
    }

    private function createCsvByDateRange($startDate, $endDate, $data)
    {
        $fileName = "sales_report_{$startDate}_{$endDate}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Order ID', 'Date', 'Customer', 'Table', 'Items', 'Total (Rp)']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row['order_id'],
                    $row['date'],
                    $row['customer_name'],
                    $row['table_number'],
                    $row['items_summary'],
                    $row['total_amount']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
