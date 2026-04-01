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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class MeteoService
{

    public function __construct(
        private HttpClientInterface $httpClient,
        private OpenWeatherMapToMeteoMapper $meteoMapper,
        private Security $security,
        private TagAwareCacheInterface $cachePool,
        #[Autowire('%env(OPENWEATHERMAP_API_KEY)%')] private string $apiKey
    )
    {
    }

    public function getWeather(?string $city = null) : Meteo
    {
        if (null === $city) {
            $city = $this->getCity();
        }

        return $this->meteoFromCache(cacheId:'meteo_'.$city, city: $city);
    }

    protected function getCity()
    {
        $currentUser = $this->security->getUser();
        $city = $currentUser->getCity();
        return $city;
    }

    protected function meteoFromCache(string $cacheId, ?string $city): mixed
    {
        $meteo = $this->cachePool->get($cacheId,
            function (ItemInterface $item) use ($city) {
                $item->expiresAfter($this->cacheExpirationDelay());
                return $this->meteoFromApi($city);
            }
        );
        return $meteo;
    }

    private function cacheExpirationDelay()
    {
        $dateNow = new \DateTimeImmutable();
        $tomorrow = $dateNow->modify('+1 day')->setTime(0, 0, 0);
        $diff = $tomorrow->getTimestamp() - $dateNow->getTimestamp();
        return $diff;
    }

    protected function meteoFromApi(string $city): Meteo
    {
        $response = $this->callApi($city);
        $this->assertSuccessful($response);
        return $this->buildMeteo($response->toArray());
    }

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
            throw new MeteoApiException('Service météo indisponible', Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    private function buildMeteo(array $weather) : Meteo
    {
        return $this->meteoMapper->map($weather);
    }

}