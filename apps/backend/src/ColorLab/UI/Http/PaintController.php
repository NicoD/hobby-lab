<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\Paint\Command\CreatePaint\CreatePaintCommand;
use App\ColorLab\Application\Paint\Command\CreatePaint\CreatePaintCommandHandler;
use App\ColorLab\Application\Paint\Query\GetPaint\GetPaintQuery;
use App\ColorLab\Application\Paint\Query\GetPaint\GetPaintQueryHandler;
use App\ColorLab\Application\Paint\Query\ListPaints\ListPaintsQuery;
use App\ColorLab\Application\Paint\Query\ListPaints\ListPaintsQueryHandler;
use App\ColorLab\UI\Trait\Input;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/color-lab/paints', name: 'paint_')]
final class PaintController extends AbstractController
{
    use Input;

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(ListPaintsQueryHandler $handler): JsonResponse
    {
        return $this->json($handler(new ListPaintsQuery()));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, CreatePaintCommandHandler $handler): JsonResponse
    {
        $data = $request->toArray();
        $handler(new CreatePaintCommand(
            $this->asString($data['paintReferenceId']),
            $this->asString($request->headers->get('X-User-Id')),
            isset($data['purchasedAt']) ? $this->asString($data['purchasedAt']) : null,
        ));

        return $this->json(null, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id, GetPaintQueryHandler $handler): JsonResponse
    {
        $view = $handler(new GetPaintQuery($id));

        if (null === $view) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
