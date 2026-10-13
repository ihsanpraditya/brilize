<?php

declare(strict_types=1);

namespace App\DTO\Siswa;

use Symfony\Component\HttpFoundation\Request;

final readonly class ImportSiswaBatchDTO
{
    /**
     * @param list<ImportSiswaItemDTO> $items
     */
    public function __construct(
        public array $items,
        public ?int $defaultKelasId = null,
        public string $onDuplicate = 'skip', // 'skip' | 'update'
        public bool $createUserAccount = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $payload = $request->getPayload();

        $defaultKelasId = $payload->has('default_kelas_id') && $payload->get('default_kelas_id') !== ''
            ? (int) $payload->get('default_kelas_id')
            : ($request->request->has('default_kelas_id') && $request->request->get('default_kelas_id') !== '' ? (int) $request->request->get('default_kelas_id') : null);

        $onDuplicate = $payload->getString('on_duplicate', (string) $request->request->get('on_duplicate', 'skip'));
        if (!in_array($onDuplicate, ['skip', 'update'], true)) {
            $onDuplicate = 'skip';
        }

        $createUserAccount = $payload->getBoolean('create_user_account', (bool) $request->request->get('create_user_account', true));

        $rawItems = $payload->all('items');
        if (empty($rawItems)) {
            $rawItems = $request->request->all('items');
        }

        $items = [];
        if (is_array($rawItems)) {
            foreach ($rawItems as $rawItem) {
                if (is_array($rawItem) && !empty($rawItem['nis'] ?? '') && !empty($rawItem['nama_lengkap'] ?? $rawItem['namaLengkap'] ?? '')) {
                    $items[] = ImportSiswaItemDTO::fromArray($rawItem);
                }
            }
        }

        return new self(
            items: $items,
            defaultKelasId: $defaultKelasId,
            onDuplicate: $onDuplicate,
            createUserAccount: $createUserAccount,
        );
    }
}
