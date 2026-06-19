<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\PaintType\Command\CreatePaintType\CreatePaintTypeCommand;
use App\ColorLab\Application\PaintType\Command\CreatePaintType\CreatePaintTypeCommandHandler;
use App\ColorLab\Application\PaintType\Query\GetPaintType\GetPaintTypeQuery;
use App\ColorLab\Application\PaintType\Query\GetPaintType\GetPaintTypeQueryHandler;
use App\ColorLab\Application\PaintType\Query\ListPaintTypes\ListPaintTypesQuery;
use App\ColorLab\Application\PaintType\Query\ListPaintTypes\ListPaintTypesQueryHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'paint_type_')]
final class PaintTypeController extends AbstractController
{
    #[Route('/color-lab/paint-types', name: 'paint_type_list', methods: ['GET'])]
    public function list(ListPaintTypesQueryHandler $handler): JsonResponse
    {
        return $this->json($handler(new ListPaintTypesQuery()));
    }

    #[Route('/color-lab/paint-types', name: 'paint_type_create', methods: ['POST'])]
    public function create(Request $request, CreatePaintTypeCommandHandler $handler): JsonResponse
    {
        $handler(new CreatePaintTypeCommand(
            $request->getPayload()->getString('name'),
            $request->headers->get('X-User-Id') ?? '',
        ));

        return $this->json(null, Response::HTTP_CREATED);
    }

    #[Route('/color-lab/paint-types/{handle}', name: 'paint_type_get', methods: ['GET'])]
    public function get(string $handle, GetPaintTypeQueryHandler $handler): JsonResponse
    {
        $view = $handler(new GetPaintTypeQuery($handle));

        if (!$view instanceof \App\ColorLab\Application\PaintType\ReadModel\PaintTypeView) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
