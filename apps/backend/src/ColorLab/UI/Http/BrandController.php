<?php

declare(strict_types=1);

namespace App\ColorLab\UI\Http;

use App\ColorLab\Application\Brand\Command\CreateBrand\CreateBrandCommand;
use App\ColorLab\Application\Brand\Command\CreateBrand\CreateBrandCommandHandler;
use App\ColorLab\Application\Brand\Query\GetBrand\GetBrandQuery;
use App\ColorLab\Application\Brand\Query\GetBrand\GetBrandQueryHandler;
use App\ColorLab\Application\Brand\Query\ListBrands\ListBrandsQuery;
use App\ColorLab\Application\Brand\Query\ListBrands\ListBrandsQueryHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/color-lab/brands', name: 'brand_')]
final class BrandController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(ListBrandsQueryHandler $handler): JsonResponse
    {
        return $this->json($handler(new ListBrandsQuery()));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, CreateBrandCommandHandler $handler): JsonResponse
    {
        $data = $request->toArray();
        $handler(new CreateBrandCommand(
            $data['handle'],
            $data['name'],
            $request->headers->get('X-User-Id'),
        ));

        return $this->json(null, Response::HTTP_CREATED);
    }

    #[Route('/{handle}', name: 'get', methods: ['GET'])]
    public function get(string $handle, GetBrandQueryHandler $handler): JsonResponse
    {
        $view = $handler(new GetBrandQuery($handle));

        if (null === $view) {
            throw new NotFoundHttpException();
        }

        return $this->json($view);
    }
}
