<?php

namespace App\Controller;

use App\Enum\Month;
use App\Repository\AdviceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
}
