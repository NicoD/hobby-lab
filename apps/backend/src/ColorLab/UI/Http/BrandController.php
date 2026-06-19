<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\Brand\Command\CreateBrand\CreateBrandCommand;
use App\ColorLab\Application\Brand\Command\CreateBrand\CreateBrandCommandHandler;
use App\ColorLab\Application\Brand\Query\GetBrand\GetBrandQuery;
use App\ColorLab\Application\Brand\Query\GetBrand\GetBrandQueryHandler;
use App\ColorLab\Application\Brand\Query\ListBrands\ListBrandsQuery;
use App\ColorLab\Application\Brand\Query\ListBrands\ListBrandsQueryHandler;
use App\ColorLab\UI\Trait\Input;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'brand_')]
final class BrandController extends AbstractController
{
    use Input;

    #[Route('/color-lab/brands', name: 'brand_list', methods: ['GET'])]
    public function list(ListBrandsQueryHandler $handler): JsonResponse
    {
        return $this->json($handler(new ListBrandsQuery()));
    }

    #[Route('/color-lab/brands', name: 'brand_create', methods: ['POST'])]
    public function create(Request $request, CreateBrandCommandHandler $handler): JsonResponse
    {
        $data = $request->toArray();
        $handler(new CreateBrandCommand(
            $this->asString($data['name']),
            $this->asString($request->headers->get('X-User-Id')),
        ));

        return $this->json(null, Response::HTTP_CREATED);
    }

    #[Route('/color-lab/brands/{handle}', name: 'brand_get', methods: ['GET'])]
    public function get(string $handle, GetBrandQueryHandler $handler): JsonResponse
    {
        $view = $handler(new GetBrandQuery($handle));

        if (!$view instanceof \App\ColorLab\Application\Brand\ReadModel\BrandView) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
