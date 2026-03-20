<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Enum\Month;
use App\Repository\AdviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class AdviceController extends AbstractController
{
    #[Route('/advices-index', name: 'advices_index')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AdviceController.php',
        ]);
    }

    #[Route('/advices/{monthId}', name: 'advices_list_with_month', requirements: ['monthId' => '\d{1,2}'], methods: 'GET')]
    #[Route('/advices', name: 'advices_list', methods: 'GET')]
    public function list(AdviceRepository $adviceRepository, SerializerInterface $serializer, ?int $monthId = null): JsonResponse
    {
        if ($monthId) {
            $month = Month::from($monthId);
        } else {
            $month = Month::fromCurMonth();
        }
        $advices = $adviceRepository->findByMonth($month);
        $advicesSerialized = $serializer->serialize($advices, 'json', ['groups' => 'getAdvices']);
        return new JsonResponse($advicesSerialized, Response::HTTP_OK, [], true);
    }

    #[Route('/advices/{id}', name: 'advices_update', methods: 'PUT')]
    public function updateAdvice(Advice $currentAdvice, Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $requestContent = $request->getContent();
        $updatedAdvice = $serializer->deserialize(
            $requestContent,
            Advice::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAdvice]
        );
        $em->persist($updatedAdvice);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/advices/{id}', name: 'advices_delete', methods: 'DELETE')]
    public function deleteAdvice(Advice $advice, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($advice);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/advices', name: 'advices_create', methods: 'POST')]
    public function createAdvice(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $requestContent = $request->getContent();
        $newAdvice = $serializer->deserialize(
            $requestContent,
            Advice::class,
            'json'
        );
        $em->persist($newAdvice);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
