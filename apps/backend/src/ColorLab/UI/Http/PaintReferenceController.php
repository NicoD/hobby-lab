<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\PaintReference\Command\CreatePaintReference\CreatePaintReferenceCommand;
use App\ColorLab\Application\PaintReference\Query\GetPaintReference\GetPaintReferenceQuery;
use App\ColorLab\Application\PaintReference\Query\ListPaintReferences\ListPaintReferencesQuery;
use App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceView;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Bus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'paint_reference_')]
final class PaintReferenceController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/color-lab/paint-references', name: 'paint_reference_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        return $this->json($this->queryBus->handle(new ListPaintReferencesQuery(
            $request->headers->get('X-User-Id') ?? '',
            $request->query->get('brand'),
            $request->query->get('range'),
            $request->query->get('type'),
            $request->query->get('color'),
            $request->query->get('search'),
        )));
    }

    #[Route('/color-lab/paint-references', name: 'paint_reference_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $request->getPayload();
        $handle = $this->commandBus->handle(new CreatePaintReferenceCommand(
            $payload->getString('name'),
            $payload->has('brand') ? $payload->getString('brand') : null,
            $payload->has('range') ? $payload->getString('range') : null,
            $payload->has('paintType') ? $payload->getString('paintType') : null,
            $payload->has('color') ? $payload->getString('color') : null,
            $request->headers->get('X-User-Id') ?? '',
        ));

        return $this->json($this->queryBus->handle(new GetPaintReferenceQuery((string) $handle)), Response::HTTP_CREATED);
    }

    #[Route('/color-lab/paint-references/{handle}', name: 'paint_reference_get', methods: ['GET'])]
    public function get(string $handle): JsonResponse
    {
        $view = $this->queryBus->handle(new GetPaintReferenceQuery($handle));

        if (!$view instanceof PaintReferenceView) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
