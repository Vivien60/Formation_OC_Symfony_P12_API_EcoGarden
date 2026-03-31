<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\{DependencyInjection\Attribute\Autowire,
    EventDispatcher\EventSubscriberInterface,
    HttpFoundation\JsonResponse,
    HttpKernel\Event\ExceptionEvent,
    HttpKernel\Exception\HttpException};
use App\Exception\ConstraintViolationException;

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
            default => $this->buildDebugMsg(),
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
        return $this->buildResponse(status:404, message:'La route n\'existe pas');
    }

    private function buildRedactedMsg(): array
    {
        $status = $this->getStatus();
        $message = match ($status) {
            400 => 'Requete mal formulée',
            401 => 'Non autorisé',
            403 => 'Accès refusé',
            404 => 'Ressource non trouvée',
            default => 'Erreur',
        };
        $erreurs = match ($status) {
            400 => $this->exception->getMessage(),
            default => [],
        };

        return $this->buildResponse(status:$status, message:$message, erreurs:$erreurs);
    }

    private function buildDefaultMessage() : array
    {
        return $this->buildResponse(status:$this->getStatus(), message:'Erreur');
    }

    protected function buildDebugMsg(): array
    {
        return $this->buildResponse(status:$this->getStatus(), message:$this->exception->getMessage());
    }

    private function getStatus(): int
    {
        return $this->exception instanceof HttpException
            ? $this->exception->getStatusCode()
            : 500;
    }

    protected function buildResponse(int $status, string $message, ?array $erreurs = null): array
    {
        $erreurs = $erreurs ?? new \stdClass();
        return [
            'status' => $status,
            'message' => $message,
            'errors' => $erreurs,
        ];
    }
}
