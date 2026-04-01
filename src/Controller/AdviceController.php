<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Exception\ConstraintViolationException;
use App\Repository\AdviceRepository;
use App\Service\AdviceMonthManager;
use App\Service\PaginatedResponseFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\{Bundle\FrameworkBundle\Controller\AbstractController,
    Component\HttpFoundation\JsonResponse,
    Component\HttpFoundation\Request,
    Component\HttpFoundation\Response,
    Component\HttpKernel\Exception\HttpException,
    Component\Routing\Attribute\Route,
    Component\Serializer\Normalizer\AbstractNormalizer,
    Component\Serializer\SerializerInterface,
    Component\Validator\ConstraintViolation,
    Component\Validator\ConstraintViolationList,
    Component\Validator\Validator\ValidatorInterface};
use App\Enum\Month;

final class AdviceController extends AbstractController
{

    public function __construct(private PaginatedResponseFormatter $paginatedResponseFormatter  )
    {
    }

    #[Route('/conseil', name: 'advices_list', methods: 'GET')]
    public function index(Request $request, AdviceRepository $adviceRepository): JsonResponse
    {
        $month = Month::fromCurMonth();
        $page = $request->query->getInt('page', 1);
        if($page < 1) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Le paramètre "page" doit être supérieur à 0');
        }
        $limit = $this->getParameter('pagination_limit');

        $advices = $adviceRepository->findByMonthWithPagination($month, $page, $limit);
        $advicesSerialized = $this->paginatedResponseFormatter->format(paginatedItems: $advices, limit: $limit, page: $page, groups: ['getAdvices']);
        return new JsonResponse($advicesSerialized, Response::HTTP_OK, [], true);
    }

    #[Route('/conseil/{monthId}', name: 'advices_list_with_month', requirements: ['monthId' => '\d+'], methods: 'GET')]
    public function list(Request $request, AdviceRepository $adviceRepository, int $monthId): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $this->getParameter('pagination_limit');
        $month = Month::tryFrom($monthId);

        if($month === null) {
            $violations = new ConstraintViolationList([
                new ConstraintViolation(
                    'Le mois doit être compris entre 1 et 12',
                    null, [], null, 'month', $monthId
                )
            ]);
            throw new ConstraintViolationException($violations);
        }
        $advices = $adviceRepository->findByMonthWithPagination($month, $page, $this->getParameter('pagination_limit'));
        $advicesSerialized = $this->paginatedResponseFormatter->format(paginatedItems:$advices, limit:$limit, page:$page, groups:['getAdvices']);
        return new JsonResponse($advicesSerialized, Response::HTTP_OK, [], true);
    }

    #[Route('/conseil/{id}', name: 'advices_update', requirements: ['id' => '\d+'], methods: 'PUT')]
    public function updateAdvice(Advice $currentAdvice, Request $request, EntityManagerInterface $em, SerializerInterface $serializer, AdviceMonthManager $monthManager, ValidatorInterface $validator): JsonResponse
    {
        $updatedAdvice = $serializer->deserialize(
            $request->getContent(),
            Advice::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAdvice]
        );
        $months = $request->toArray()['months'] ?? [];
        $monthManager->syncMonths($updatedAdvice, $months, true);
        $errors = $validator->validate($updatedAdvice);
        if (count($errors) > 0) {
            throw new ConstraintViolationException($errors);
        }
        $em->persist($updatedAdvice);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/conseil/{id}', name: 'advices_delete', requirements: ['id' => '\d+'], methods: 'DELETE')]
    public function deleteAdvice(Advice $advice, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($advice);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/conseil', name: 'advices_create', methods: 'POST')]
    public function createAdvice(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, AdviceMonthManager $monthManager, ValidatorInterface $validator): JsonResponse
    {
        /** @var Advice $newAdvice */
        $newAdvice = $serializer->deserialize(
            $request->getContent(),
            Advice::class,
            'json'
        );
        $errors = $validator->validate($newAdvice);
        if (count($errors) > 0) {
            throw new ConstraintViolationException($errors);
        }
        $months = $request->toArray()['months'] ?? [];
        $monthManager->syncMonths($newAdvice, $months);
        $em->persist($newAdvice);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
