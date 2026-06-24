<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\UI\Http;

use App\ColorLab\Catalog\Application\PaintType\Command\CreatePaintType\CreatePaintTypeCommand;
use App\ColorLab\Catalog\Application\PaintType\Query\GetPaintType\GetPaintTypeQuery;
use App\ColorLab\Catalog\Application\PaintType\Query\ListPaintTypes\ListPaintTypesQuery;
use App\ColorLab\Catalog\Application\PaintType\ReadModel\PaintTypeView;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Bus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'catalog_paint_type_')]
final class PaintTypeController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/color-lab/catalog/paint-types', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->queryBus->handle(new ListPaintTypesQuery()));
    }

    #[Route('/color-lab/catalog/paint-types', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $handle = $this->commandBus->handle(new CreatePaintTypeCommand(
            $request->getPayload()->getString('name'),
            $request->headers->get('X-User-Id') ?? '',
        ));

        return $this->json($this->queryBus->handle(new GetPaintTypeQuery((string) $handle)), Response::HTTP_CREATED);
    }

    #[Route('/color-lab/catalog/paint-types/{handle}', name: 'get', methods: ['GET'])]
    public function get(string $handle): JsonResponse
    {
        $view = $this->queryBus->handle(new GetPaintTypeQuery($handle));

        if (!$view instanceof PaintTypeView) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
