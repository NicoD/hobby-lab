<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\PaintType\Command\CreatePaintType\CreatePaintTypeCommand;
use App\ColorLab\Application\PaintType\Command\CreatePaintType\CreatePaintTypeCommandHandler;
use App\ColorLab\Application\PaintType\Query\GetPaintType\GetPaintTypeQuery;
use App\ColorLab\Application\PaintType\Query\GetPaintType\GetPaintTypeQueryHandler;
use App\ColorLab\Application\PaintType\Query\ListPaintTypes\ListPaintTypesQuery;
use App\ColorLab\Application\PaintType\Query\ListPaintTypes\ListPaintTypesQueryHandler;
use App\ColorLab\UI\Trait\Input;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/color-lab/paint-types', name: 'paint_type_')]
final class PaintTypeController extends AbstractController
{
    use Input;

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(ListPaintTypesQueryHandler $handler): JsonResponse
    {
        return $this->json($handler(new ListPaintTypesQuery()));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, CreatePaintTypeCommandHandler $handler): JsonResponse
    {
        $data = $request->toArray();
        $handler(new CreatePaintTypeCommand(
            $this->asString($data['name']),
            $this->asString($request->headers->get('X-User-Id')),
        ));

        return $this->json(null, Response::HTTP_CREATED);
    }

    #[Route('/{handle}', name: 'get', methods: ['GET'])]
    public function get(string $handle, GetPaintTypeQueryHandler $handler): JsonResponse
    {
        $view = $handler(new GetPaintTypeQuery($handle));

        if (null === $view) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
