<?php

namespace App\Services;

use App\Interfaces\TableRepoInterface;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableService
{
    protected $tableRepository;

    public function __construct(TableRepoInterface $tableRepository)
    {
        $this->tableRepository = $tableRepository;
    }

    public function getAllTables()
    {
        return $this->tableRepository->getAll();
    }

    public function getTableById($id)
    {
        return $this->tableRepository->getById($id);
    }

    public function createTable(array $data)
    {
        $data['qr_uuid'] = (string) Str::uuid();

        return $this->tableRepository->create($data);
    }

    public function deleteTable($id)
    {
        return $this->tableRepository->delete($id);
    }

    public function generateQrCode($id)
    {
        $table = $this->tableRepository->getById($id);

        $frontendUrl = config('app.frontend_url', 'http://localhost:5173') . '/scan?table_uuid=' . $table->qr_uuid;

        return QrCode::format('svg')
            ->size(300)
            ->errorCorrection('H')
            ->generate($frontendUrl);
    }
}
