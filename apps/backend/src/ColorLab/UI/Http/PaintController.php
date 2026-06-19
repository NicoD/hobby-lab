<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\Paint\Command\CreatePaint\CreatePaintCommand;
use App\ColorLab\Application\Paint\Command\CreatePaint\CreatePaintCommandHandler;
use App\ColorLab\Application\Paint\Query\GetPaint\GetPaintQuery;
use App\ColorLab\Application\Paint\Query\GetPaint\GetPaintQueryHandler;
use App\ColorLab\Application\Paint\Query\ListPaints\ListPaintsQuery;
use App\ColorLab\Application\Paint\Query\ListPaints\ListPaintsQueryHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'paint_')]
final class PaintController extends AbstractController
{
    #[Route('/color-lab/paints', name: 'paint_list', methods: ['GET'])]
    public function list(ListPaintsQueryHandler $handler): JsonResponse
    {
        return $this->json($handler(new ListPaintsQuery()));
    }

    #[Route('/color-lab/paints', name: 'paint_create', methods: ['POST'])]
    public function create(Request $request, CreatePaintCommandHandler $handler): JsonResponse
    {
        $payload = $request->getPayload();
        $handler(new CreatePaintCommand(
            $payload->getString('paintReferenceId'),
            $request->headers->get('X-User-Id') ?? '',
            $payload->has('purchasedAt') ? $payload->getString('purchasedAt') : null,
        ));

        return $this->json(null, Response::HTTP_CREATED);
    }

    #[Route('/color-lab/paints/{id}', name: 'paint_get', methods: ['GET'])]
    public function get(string $id, GetPaintQueryHandler $handler): JsonResponse
    {
        $view = $handler(new GetPaintQuery($id));

        if (!$view instanceof \App\ColorLab\Application\Paint\ReadModel\PaintView) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
