<?php

namespace App\Service;

use App\DTO\Meteo;
use App\Exception\MeteoApiException;
use App\Mapper\OpenWeatherMapToMeteoMapper;
use Symfony\Contracts\HttpClient\{Exception\ClientExceptionInterface,
    Exception\RedirectionExceptionInterface,
    Exception\ServerExceptionInterface,
    Exception\TransportExceptionInterface,
    HttpClientInterface,
    ResponseInterface};
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MeteoService
{

    public function __construct(
        private HttpClientInterface $httpClient,
        private OpenWeatherMapToMeteoMapper $meteoMapper,
        private Security $security,
        #[Autowire('%env(OPENWEATHERMAP_API_KEY)%')] private string $apiKey
    )
    {
    }

    public function getWeather(?string $city = null) : Meteo
    {
        $currentUser = $this->security->getUser();
        $city ??= $currentUser->getCity();
        $response = $this->callApi($city);
        $this->assertSuccessful($response);
        return $this->buildMeteo($response->toArray());
    }

    /**
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */

    private function callApi(string $city): ResponseInterface
    {
        $baseUri = 'https://api.openweathermap.org';
        $endpoint = '/data/2.5/weather?units=metrics&q=%s&appid=%s';
        $urlApi = $baseUri . sprintf($endpoint, $city, $this->apiKey);
        //https://api.openweathermap.org/data/2.5/weather?q={city name},{country code}&appid={API key}
        return $this->httpClient->request(
            'GET',
            $urlApi
        );
    }

    private function buildMeteo(array $weather) : Meteo
    {
        return $this->meteoMapper->map($weather);
    }

    private function assertSuccessful(?ResponseInterface $response) : void
    {
        try {
            $response->getContent();
        } catch (
            ClientExceptionInterface|
            RedirectionExceptionInterface|
            ServerExceptionInterface $exception) {
            throw new MeteoApiException($exception->getResponse()->toArray(false)['message'], $exception->getResponse()->getStatusCode());
        } catch (TransportExceptionInterface $exception) {
            throw new MeteoApiException('Service météo indisponible', 503);
        }
    }
}