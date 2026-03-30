<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use App\Exception\ConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private ?ExceptionEvent $event = null;
    private \Throwable $exception;

    public function __construct(
        private LoggerInterface $logger,
        #[Autowire('%kernel.environment%')] private string $environment,
    ) {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onExceptionEvent',
        ];
    }

    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $this->event = $event;
        $this->exception = $event->getThrowable();
        if($this->environment === 'prod') {
            $message = $this->handleRedactedMsg();
        } else {
            $message = $this->handleDebugMsg();
        }

        $this->logger->error('API Error : '.$this->exception->getMessage(), [
            'exception' => $this->exception,
            'trace' => $this->exception->getTraceAsString(),
        ]);

        $event->setResponse(new JsonResponse($message, $message['status']));
    }

    private function handleDebugMsg() : array
    {
        $message = match (true) {
            $this->exception instanceof ConstraintViolationException => $this->buildConstraintViolationMsg(),
            $this->exception instanceof HttpException => $this->buildDebugMsg(),
            //les autres sont des méthodes non détaillées pour un debug
            default => $this->handleRedactedMsg(),
        };
        return $message;
    }

    private function handleRedactedMsg() : array
    {
        $message = match (true) {
            $this->exception instanceof ConstraintViolationException => $this->buildConstraintViolationMsg(),
            $this->isRouteNotFoundException() => $this->buildRouteNotFoundMsg(),
            $this->exception instanceof HttpException => $this->buildRedactedMsg(),
            default => $this->buildDefaultMessage(),
        };
        return $message;

    }

    /**
     * @param ConstraintViolationException $exception
     * @return array
     */
    protected function buildConstraintViolationMsg(): array
    {
        $data = ['status' => $this->getStatus(), 'errors' => [], 'message' => $this->exception->getMessage()];
        foreach ($this->exception->getErrors() as $constraintErr) {
            $data['errors'][$constraintErr->getPropertyPath()] = $constraintErr->getMessage();
        }
        return $data;
    }

    private function isRouteNotFoundException(): bool
    {
        return $this->getStatus() === 404 && $this->exception->getPrevious() instanceof \Symfony\Component\Routing\Exception\ResourceNotFoundException;
    }

    protected function buildRouteNotFoundMsg(): array
    {
        return [
            'status' => 400,
            'message' => 'La route n\'existe pas'
        ];
    }

    private function buildRedactedMsg(): array
    {
        $status = $this->getStatus();
        $message = match ($status) {
            400 => 'Requete mal formulée',
            401 => 'Non autorisé',
            403 => 'Accès refusé',
            404 => 'Ressource non trouvée',
            default => 'Erreur serveur',
        };

        return [
            'status' => $status,
            'message' => $message,
        ];
    }

    private function getStatus(): int
    {
        return $this->exception instanceof HttpException
            ? $this->exception->getStatusCode()
            : 500;
    }

    private function buildDefaultMessage() : array
    {
        return [
            'status' => $this->getStatus(),
            'message' => $this->exception->getMessage()
        ];
    }

    /**
     * @param $exception
     * @return array
     */
    protected function buildDebugMsg(): array
    {
        return [
            'status' => $this->getStatus(),
            'message' => $this->exception->getMessage()
        ];
    }
}
