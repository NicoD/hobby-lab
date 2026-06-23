<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\Paint\Command\CreatePaint\CreatePaintCommand;
use App\ColorLab\Application\Paint\Query\GetPaint\GetPaintQuery;
use App\ColorLab\Application\Paint\Query\ListPaints\ListPaintsQuery;
use App\ColorLab\Application\Paint\ReadModel\PaintView;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Bus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'paint_')]
final class PaintController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/color-lab/paints', name: 'paint_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->queryBus->handle(new ListPaintsQuery()));
    }

    #[Route('/color-lab/paints', name: 'paint_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $request->getPayload();
        $id = $this->commandBus->handle(new CreatePaintCommand(
            $payload->getString('paintReferenceId'),
            $request->headers->get('X-User-Id') ?? '',
            $payload->has('purchasedAt') ? $payload->getString('purchasedAt') : null,
        ));

        return $this->json($this->queryBus->handle(new GetPaintQuery((string) $id)), Response::HTTP_CREATED);
    }

    #[Route('/color-lab/paints/{id}', name: 'paint_get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $view = $this->queryBus->handle(new GetPaintQuery($id));

        if (!$view instanceof PaintView) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
