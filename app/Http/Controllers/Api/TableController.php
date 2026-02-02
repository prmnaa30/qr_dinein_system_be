<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTableRequest;
use App\Http\Resources\TableResource;
use App\Models\Table;
use App\Services\TableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Authenticated;

#[Group('Table Management', 'APIs for managing tables in the system.')]
class TableController extends Controller
{
    protected $tableService;

    public function __construct(TableService $tableService)
    {
        $this->tableService = $tableService;
    }

    #[Endpoint('Daftar Meja', 'Menampilkan daftar semua meja yang tersedia.')]
    #[Authenticated]
    #[Response(content: '[{"id": 1,"table_number": 1,"qr_uuid": "abc123","created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T00:00.000000Z"}]', status: 200)]
    public function index()
    {
        Gate::authorize('viewAny', Table::class);
        $tables = $this->tableService->getAllTables();

        return TableResource::collection($tables);
    }

    #[Endpoint('Buat Meja Baru', 'Membuat meja baru dengan data yang diberikan.')]
    #[Authenticated]
    public function store(StoreTableRequest $request)
    {
        Gate::authorize('create', Table::class);
        $table = $this->tableService->createTable($request->validated());

        return new TableResource($table);
    }

    // #[Endpoint('Tampilkan Meja', 'Menampilkan detail meja berdasarkan ID.')]
    // #[Authenticated]
    // #[Response(content: '{"id": 1,"table_number": 1,"qr_uuid": "abc123","created_at": "2023-01-01T00:00.000Z","updated_at": "2023-01-01T00:00.000000Z"}', status: 200)]
    public function show(string $id)
    {
        // unused
    }

    // #[Endpoint('Perbarui Meja', 'Memperbarui data meja berdasarkan ID.')]
    // #[Authenticated]
    public function update(Request $request, string $id)
    {
        // unused
    }

    #[Endpoint('Hapus Meja', 'Menghapus meja berdasarkan ID.')]
    #[Authenticated]
    #[Response(content: '{"message": "Meja berhasil dihapus"}', status: 200)]
    #[Response(content: '{"message": "Error message"}', status: 400)]
    public function destroy(string $id)
    {
        $tableTarget = $this->tableService->getTableById($id);
        Gate::authorize('delete', $tableTarget);

        try {
            $this->tableService->deleteTable($id);
            return response()->json(['message' => 'Meja berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    #[Endpoint('Unduh Kode QR Meja', 'Mengunduh atau melihat pratinjau kode QR berdasarkan field qr_uuid meja.')]
    #[Authenticated]
    #[Response(content: '{"qr_code": "data:image/png;base64..."}', status: 200)]
    public function downloadQr($id)
    {
        Gate::authorize('downloadQr', Table::class);
        $qrCodeBase64 = $this->tableService->generateQrCode($id);

        return response()->json([
            'qr_code' => $qrCodeBase64
        ]);
    }

    #[Endpoint('Resolve QR UUID', 'Mengubah UUID dari URL Params menjadi ID Table.')]
    #[Response('{"id": "1","table_number":"Meja 01"}', 200)]
    public function resolveUuid($uuid)
    {
        $table = Table::where('qr_uuid', $uuid)->firstOrFail();

        return response()->json([
            'id' => $table->id,
            'table_number' => $table->table_number
        ]);
    }
}
