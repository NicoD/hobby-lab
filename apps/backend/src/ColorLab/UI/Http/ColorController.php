<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\Color\Command\CreateColor\CreateColorCommand;
use App\ColorLab\Application\Color\Query\GetColor\GetColorQuery;
use App\ColorLab\Application\Color\Query\ListColors\ListColorsQuery;
use App\ColorLab\Application\Color\ReadModel\ColorView;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Bus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'color_')]
final class ColorController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/color-lab/colors', name: 'color_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->queryBus->handle(new ListColorsQuery()));
    }

    #[Route('/color-lab/colors', name: 'color_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $handle = $this->commandBus->handle(new CreateColorCommand(
            $request->getPayload()->getString('name'),
            $request->headers->get('X-User-Id') ?? '',
        ));

        return $this->json($this->queryBus->handle(new GetColorQuery((string) $handle)), Response::HTTP_CREATED);
    }

    #[Route('/color-lab/colors/{handle}', name: 'color_get', methods: ['GET'])]
    public function get(string $handle): JsonResponse
    {
        $view = $this->queryBus->handle(new GetColorQuery($handle));

        if (!$view instanceof ColorView) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
