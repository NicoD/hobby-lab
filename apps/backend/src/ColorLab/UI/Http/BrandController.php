<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\Brand\Command\CreateBrand\CreateBrandCommand;
use App\ColorLab\Application\Brand\Query\GetBrand\GetBrandQuery;
use App\ColorLab\Application\Brand\Query\ListBrands\ListBrandsQuery;
use App\ColorLab\Application\Brand\ReadModel\BrandView;
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

#[Route(name: 'brand_')]
final class BrandController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
        #[Autowire('%app.pagination.default_limit%')]
        private readonly int $defaultPaginationLimit,
    ) {
    }

    #[Route('/color-lab/brands', name: 'brand_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $ownedBy = $request->headers->get('X-User-Id') ?? '';
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', $this->defaultPaginationLimit)));
        $sortRaw = $request->query->getString('sort', 'name');
        $sort = \in_array($sortRaw, ['name', 'createdAt'], true) ? $sortRaw : 'name';
        $dir = 'desc' === strtolower($request->query->getString('dir', 'asc')) ? 'desc' : 'asc';
        $search = $request->query->getString('search') ?: null;

        return $this->json($this->queryBus->handle(new ListBrandsQuery(
            $ownedBy,
            new Pagination($page, $limit),
            new SortOrder($sort, $dir),
            $search,
        )));
    }

    #[Route('/color-lab/brands', name: 'brand_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $handle = $this->commandBus->handle(new CreateBrandCommand(
            $request->getPayload()->getString('name'),
            $request->headers->get('X-User-Id') ?? '',
        ));

        return $this->json($this->queryBus->handle(new GetBrandQuery((string) $handle)), Response::HTTP_CREATED);
    }

    #[Route('/color-lab/brands/{handle}', name: 'brand_get', methods: ['GET'])]
    public function get(string $handle): JsonResponse
    {
        $view = $this->queryBus->handle(new GetBrandQuery($handle));

        if (!$view instanceof BrandView) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
