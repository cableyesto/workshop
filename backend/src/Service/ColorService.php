<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ColorRepository;
use Illuminate\Support\Collection;

final readonly class ColorService
{
    public function __construct(
        private ColorRepository $colorRepository,
    ) {
    }

    /**
     * Get all colors as a collection.
     *
     * @return Collection<int, array{id: int, name: string, hexCode: string}>
     */
    public function getAllColors(): Collection
    {
        $colors = $this->colorRepository->findAll();

        return collect($colors)->map(fn ($color) => [
            'id' => $color->getId(),
            'name' => $color->getName(),
            'hexCode' => $color->getHexCode(),
        ]);
    }

    /**
     * Find a color by ID.
     *
     * @return array{id: int, name: string, hexCode: string}|null
     */
    public function getColorById(int $id): ?array
    {
        $color = $this->colorRepository->find($id);

        if (!$color) {
            return null;
        }

        return [
            'id' => $color->getId(),
            'name' => $color->getName(),
            'hexCode' => $color->getHexCode(),
        ];
    }

    /**
     * Search colors by name.
     *
     * @return Collection<int, array{id: int, name: string, hexCode: string}>
     */
    public function searchByName(string $query): Collection
    {
        $colors = $this->colorRepository->findAll();

        return collect($colors)
            ->filter(fn ($color) => str_contains(
                strtolower($color->getName()),
                strtolower($query)
            ))
            ->map(fn ($color) => [
                'id' => $color->getId(),
                'name' => $color->getName(),
                'hexCode' => $color->getHexCode(),
            ])
            ->values(); // Re-index after filter
    }
}
