<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\Color\Command\CreateColor\CreateColorCommand;
use App\ColorLab\Application\Color\Command\CreateColor\CreateColorCommandHandler;
use App\ColorLab\Application\Color\Query\GetColor\GetColorQuery;
use App\ColorLab\Application\Color\Query\GetColor\GetColorQueryHandler;
use App\ColorLab\Application\Color\Query\ListColors\ListColorsQueryHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'color_')]
final class ColorController extends AbstractController
{
    public function __construct(private readonly CreateColorCommandHandler $commandHandler, private readonly GetColorQueryHandler $queryHandler)
    {
    }

    #[Route('/color-lab/colors', name: 'color_list', methods: ['GET'])]
    public function list(ListColorsQueryHandler $handler): JsonResponse
    {
        return $this->json($handler());
    }

    #[Route('/color-lab/colors', name: 'color_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $handle = ($this->commandHandler)(new CreateColorCommand(
            $request->getPayload()->getString('name'),
            $request->headers->get('X-User-Id') ?? '',
        ));

        return $this->json(($this->queryHandler)(new GetColorQuery((string) $handle)), Response::HTTP_CREATED);
    }

    #[Route('/color-lab/colors/{handle}', name: 'color_get', methods: ['GET'])]
    public function get(string $handle, GetColorQueryHandler $handler): JsonResponse
    {
        $view = $handler(new GetColorQuery($handle));

        if (!$view instanceof \App\ColorLab\Application\Color\ReadModel\ColorView) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
