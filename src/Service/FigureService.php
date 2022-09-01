<?php

namespace App\Service;

use App\Repository\FigureRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FigureService
{
    public function __construct(
        private RequestStack $requestStack,
        private FigureRepository $figureRepo,
        private PaginatorInterface $paginator
    ) {
    }
    public function getPaginatedFigures()
    {
        $request = $this->requestStack->getMainRequest();
        $page = $request->query->getInt('page', 1);
        $limit = 4;

        $figuresQuery = $this->figureRepo->findForpagination();

        return $this->paginator->paginate($figuresQuery, $page, $limit);
    }
}
