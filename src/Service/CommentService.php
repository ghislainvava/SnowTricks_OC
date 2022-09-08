<?php

namespace App\Service;

use App\Repository\CommentRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use APP\Entity\Figure;

class CommentService
{
    public function __construct(
        private RequestStack $requestStack,
        private CommentRepository $commentRepo,
        private PaginatorInterface $paginator
    ) {
    }
    public function getPaginatedComments($limit, Figure $figure)
    {
        $request = $this->requestStack->getMainRequest();
        $page = $request->query->getInt('page', 1);
        // $limit = 5;

        $commentsQuery = $this->commentRepo->findForpagination($figure->getId());

        return $this->paginator->paginate($commentsQuery, $page, $limit);
    }
}
