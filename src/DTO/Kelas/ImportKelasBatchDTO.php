<?php

declare(strict_types=1);

namespace App\DTO\Kelas;

use Symfony\Component\HttpFoundation\Request;

final readonly class ImportKelasBatchDTO
{
    /**
     * @param list<ImportKelasItemDTO> $items
     */
    public function __construct(
        public array $items,
        public ?int $tahunPelajaranId = null,
        public string $onDuplicate = 'skip', // 'skip' | 'update'
    ) {}

    public static function fromRequest(Request $request): self
    {
        $payload = $request->getPayload();

        $tahunPelajaranId = $payload->has('tahun_pelajaran_id') && $payload->get('tahun_pelajaran_id') !== ''
            ? (int) $payload->get('tahun_pelajaran_id')
            : ($request->request->has('tahun_pelajaran_id') && $request->request->get('tahun_pelajaran_id') !== '' ? (int) $request->request->get('tahun_pelajaran_id') : null);

        $onDuplicate = $payload->getString('on_duplicate', (string) $request->request->get('on_duplicate', 'skip'));
        if (!in_array($onDuplicate, ['skip', 'update'], true)) {
            $onDuplicate = 'skip';
        }

        $rawItems = $payload->all('items');
        if (empty($rawItems)) {
            $rawItems = $request->request->all('items');
        }

        $items = [];
        if (is_array($rawItems)) {
            foreach ($rawItems as $rawItem) {
                if (is_array($rawItem) && !empty($rawItem['nama_kelas'] ?? $rawItem['namaKelas'] ?? '')) {
                    $items[] = ImportKelasItemDTO::fromArray($rawItem);
                }
            }
        }

        return new self(
            items: $items,
            tahunPelajaranId: $tahunPelajaranId,
            onDuplicate: $onDuplicate,
        );
    }
}
