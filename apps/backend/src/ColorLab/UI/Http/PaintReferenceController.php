<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\PaintReference\Command\CreatePaintReference\CreatePaintReferenceCommand;
use App\ColorLab\Application\PaintReference\Command\CreatePaintReference\CreatePaintReferenceCommandHandler;
use App\ColorLab\Application\PaintReference\Query\GetPaintReference\GetPaintReferenceQuery;
use App\ColorLab\Application\PaintReference\Query\GetPaintReference\GetPaintReferenceQueryHandler;
use App\ColorLab\Application\PaintReference\Query\ListPaintReferences\ListPaintReferencesQuery;
use App\ColorLab\Application\PaintReference\Query\ListPaintReferences\ListPaintReferencesQueryHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'paint_reference_')]
final class PaintReferenceController extends AbstractController
{
    public function __construct(private readonly CreatePaintReferenceCommandHandler $commandHandler, private readonly GetPaintReferenceQueryHandler $queryHandler)
    {
    }

    #[Route('/color-lab/paint-references', name: 'paint_reference_list', methods: ['GET'])]
    public function list(Request $request, ListPaintReferencesQueryHandler $handler): JsonResponse
    {
        return $this->json($handler(new ListPaintReferencesQuery(
            $request->headers->get('X-User-Id') ?? '',
            $request->query->get('brand'),
            $request->query->get('range'),
            $request->query->get('type'),
            $request->query->get('color'),
        )));
    }

    #[Route('/color-lab/paint-references', name: 'paint_reference_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $request->getPayload();
        $handle = ($this->commandHandler)(new CreatePaintReferenceCommand(
            $payload->getString('name'),
            $payload->getString('brandHandle'),
            $payload->getString('rangeHandle'),
            $payload->getString('paintTypeHandle'),
            $payload->has('colorHandle') ? $payload->getString('colorHandle') : null,
            $request->headers->get('X-User-Id') ?? '',
        ));

        return $this->json(($this->queryHandler)(new GetPaintReferenceQuery((string) $handle)), Response::HTTP_CREATED);
    }

    #[Route('/color-lab/paint-references/{handle}', name: 'paint_reference_get', methods: ['GET'])]
    public function get(string $handle, GetPaintReferenceQueryHandler $handler): JsonResponse
    {
        $view = $handler(new GetPaintReferenceQuery($handle));

        if (!$view instanceof \App\ColorLab\Application\PaintReference\ReadModel\PaintReferenceView) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
