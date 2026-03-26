<?php

namespace App\Controller;

use App\Exception\MeteoApiException;
use App\Service\MeteoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MeteoController extends AbstractController
{
    #[Route('/meteo/{city}', name: 'app_meteo_detail', requirements: ['city' => '\w+'], methods: 'GET')]
    #[Route('/meteo', name: 'app_meteo_detail_default', methods: 'GET')]
    public function getMeteoDetail(MeteoService $meteoService, ?string $city = null) : JsonResponse
    {
        try {
            $meteo = $meteoService->getWeather($city);

        } catch (MeteoApiException $exception) {
            return new JsonResponse(["message" => $exception->getMessage(), "code" => $exception->getCode()], $exception->getCode());
        }
        return new JsonResponse($meteo, 200, []);
    }
}
