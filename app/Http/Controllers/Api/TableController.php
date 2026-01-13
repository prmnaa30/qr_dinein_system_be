<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTableRequest;
use App\Http\Resources\TableResource;
use App\Models\Table;
use App\Services\TableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TableController extends Controller
{
    protected $tableService;

    public function __construct(TableService $tableService)
    {
        $this->tableService = $tableService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('viewAny', Table::class);
        $tables = $this->tableService->getAllTables();

        return TableResource::collection($tables);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTableRequest $request)
    {
        Gate::authorize('create', Table::class);
        $table = $this->tableService->createTable($request->validated());

        return new TableResource($table);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tableTarget = $this->tableService->getTableById($id);
        Gate::authorize('delete', $tableTarget);

        try {
            $this->tableService->deleteTable($id);
            return response()->json(['message' => 'Table deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Download/preview QR Code based on table's qr_uuid field
     */
    public function downloadQr($id)
    {
        Gate::authorize('downloadQr', Table::class);
        $qrCodeSvg = $this->tableService->generateQrCode($id);

        return response()->json([
            'svg' => (string) $qrCodeSvg
        ]);
    }
}
