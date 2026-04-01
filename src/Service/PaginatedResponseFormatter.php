<?php

namespace App\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\SerializerInterface;

class PaginatedResponseFormatter
{

    public function __construct(private SerializerInterface $serializer)
    {
    }
    /**
     * @param array $paginatedItems
     * @return string
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function format(Paginator $paginatedItems, int $limit, int $page, array $groups): string
    {
        $response = $this->mapToResponse($paginatedItems, $limit, $page);
        return $this->serializer->serialize($response, 'json', ['groups' => $groups]);
    }

    private function mapToResponse(Paginator $paginatedItems, int $limit, int $page)
    {
        return [
            'items' => $paginatedItems,
            'pagination' => [
                'limit' => $limit,
                'page' => $page,
                'totalItems' => $paginatedItems->count(),
                'totalPages' => (int) ceil($paginatedItems->count() / $limit),
            ]
        ];
    }
}