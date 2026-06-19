<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\PaintReference\Command\CreatePaintReference\CreatePaintReferenceCommand;
use App\ColorLab\Application\PaintReference\Command\CreatePaintReference\CreatePaintReferenceCommandHandler;
use App\ColorLab\Application\PaintReference\Query\GetPaintReference\GetPaintReferenceQuery;
use App\ColorLab\Application\PaintReference\Query\GetPaintReference\GetPaintReferenceQueryHandler;
use App\ColorLab\Application\PaintReference\Query\ListPaintReferences\ListPaintReferencesQuery;
use App\ColorLab\Application\PaintReference\Query\ListPaintReferences\ListPaintReferencesQueryHandler;
use App\ColorLab\UI\Trait\Input;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/color-lab/paint-references', name: 'paint_reference_')]
final class PaintReferenceController extends AbstractController
{
    use Input;

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(ListPaintReferencesQueryHandler $handler): JsonResponse
    {
        return $this->json($handler(new ListPaintReferencesQuery()));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, CreatePaintReferenceCommandHandler $handler): JsonResponse
    {
        $data = $request->toArray();
        $handler(new CreatePaintReferenceCommand(
            $this->asString($data['name']),
            $this->asString($data['brandHandle']),
            $this->asString($data['rangeHandle']),
            $this->asString($data['paintTypeHandle']),
            isset($data['colorHandle']) ? $this->asString($data['colorHandle']) : null,
            $this->asString($request->headers->get('X-User-Id')),
        ));

        return $this->json(null, Response::HTTP_CREATED);
    }

    #[Route('/{handle}', name: 'get', methods: ['GET'])]
    public function get(string $handle, GetPaintReferenceQueryHandler $handler): JsonResponse
    {
        $view = $handler(new GetPaintReferenceQuery($handle));

        if (null === $view) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
