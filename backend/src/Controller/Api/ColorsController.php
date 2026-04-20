<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\ColorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/colors', name: 'api_colors_')]
final class ColorsController extends AbstractController
{
    public function __construct(
        private readonly ColorService $colorService,
    ) {
    }

    /**
     * Get all colors.
     *
     * @return JsonResponse
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $colors = $this->colorService->getAllColors();

        return $this->json($colors->all());
    }

    /**
     * Get a single color by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $color = $this->colorService->getColorById($id);

        if (!$color) {
            return $this->json(['error' => 'Color not found'], 404);
        }

        return $this->json($color);
    }

    /**
     * Search colors by name.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            return $this->json(['error' => 'Query parameter "q" is required'], 400);
        }

        $colors = $this->colorService->searchByName($query);

        return $this->json($colors->all());
    }
}
