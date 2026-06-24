<?php

declare(strict_types=1);

namespace App\ColorLab\Catalog\UI\Http;

use App\ColorLab\Catalog\Application\Paint\Command\CreatePaint\CreatePaintCommand;
use App\ColorLab\Catalog\Application\Paint\Query\GetPaint\GetPaintQuery;
use App\ColorLab\Catalog\Application\Paint\Query\ListPaints\ListPaintsQuery;
use App\ColorLab\Catalog\Application\Paint\ReadModel\PaintView;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Query\Pagination;
use App\Shared\Application\Query\SortOrder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'catalog_paint_')]
final class PaintController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
        #[Autowire('%app.pagination.default_limit%')]
        private readonly int $defaultPaginationLimit,
    ) {
    }

    #[Route('/color-lab/catalog/paints', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $ownedBy = $request->headers->get('X-User-Id') ?? '';
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', $this->defaultPaginationLimit)));
        $sortRaw = $request->query->getString('sort', 'name');
        $sort = \in_array($sortRaw, ['name', 'createdAt'], true) ? $sortRaw : 'name';
        $dir = 'desc' === strtolower($request->query->getString('dir', 'asc')) ? 'desc' : 'asc';
        $search = $request->query->getString('search') ?: null;

        return $this->json($this->queryBus->handle(new ListPaintsQuery(
            $ownedBy,
            new Pagination($page, $limit),
            new SortOrder($sort, $dir),
            $search,
        )));
    }

    #[Route('/color-lab/catalog/paints', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $request->getPayload();
        $handle = $this->commandBus->handle(new CreatePaintCommand(
            $payload->getString('name'),
            $payload->has('brand') ? $payload->getString('brand') : null,
            $payload->has('range') ? $payload->getString('range') : null,
            $payload->has('paintType') ? $payload->getString('paintType') : null,
            $payload->has('color') ? $payload->getString('color') : null,
            $request->headers->get('X-User-Id') ?? '',
        ));

        return $this->json($this->queryBus->handle(new GetPaintQuery((string) $handle)), Response::HTTP_CREATED);
    }

    #[Route('/color-lab/catalog/paints/{handle}', name: 'get', methods: ['GET'])]
    public function get(string $handle): JsonResponse
    {
        $view = $this->queryBus->handle(new GetPaintQuery($handle));

        if (!$view instanceof PaintView) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
