<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Exception\ConstraintViolationException;
use App\Repository\AdviceRepository;
use App\Service\AdviceMonthManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\{Bundle\FrameworkBundle\Controller\AbstractController,
    Component\HttpFoundation\JsonResponse,
    Component\HttpFoundation\Request,
    Component\HttpFoundation\Response,
    Component\Routing\Attribute\Route,
    Component\Serializer\Normalizer\AbstractNormalizer,
    Component\Serializer\SerializerInterface,
    Component\Validator\ConstraintViolation,
    Component\Validator\ConstraintViolationList,
    Component\Validator\Validator\ValidatorInterface};
use App\Enum\Month;

final class AdviceController extends AbstractController
{
    #[Route('/conseil', name: 'advices_list', methods: 'GET')]
    public function index(AdviceRepository $adviceRepository, SerializerInterface $serializer): JsonResponse
    {
        $month = Month::fromCurMonth();

        $advices = $adviceRepository->findByMonthWithPagination($month, 1, $this->getParameter('pagination_limit'));
        $advicesSerialized = $serializer->serialize($advices, 'json', ['groups' => 'getAdvices']);
        return new JsonResponse($advicesSerialized, Response::HTTP_OK, [], true);
    }

    #[Route('/conseil/{monthId}', name: 'advices_list_with_month', requirements: ['monthId' => '\d+'], methods: 'GET')]
    public function list(AdviceRepository $adviceRepository, SerializerInterface $serializer, int $monthId): JsonResponse
    {
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
        $advices = $adviceRepository->findByMonthWithPagination($month, 1, $this->getParameter('pagination_limit'));
        $advicesSerialized = $serializer->serialize($advices, 'json', ['groups' => 'getAdvices']);
        return new JsonResponse($advicesSerialized, Response::HTTP_OK, [], true);
    }

    #[Route('/conseil/{id}', name: 'advices_update', methods: 'PUT')]
    public function updateAdvice(Advice $currentAdvice, Request $request, EntityManagerInterface $em, SerializerInterface $serializer, AdviceMonthManager $monthManager): JsonResponse
    {
        $updatedAdvice = $serializer->deserialize(
            $request->getContent(),
            Advice::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAdvice]
        );
        $months = $request->toArray()['months'] ?? [];
        $monthManager->syncMonths($updatedAdvice, $months, true);
        $em->persist($updatedAdvice);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/conseil/{id}', name: 'advices_delete', methods: 'DELETE')]
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
