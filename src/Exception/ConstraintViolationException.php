<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ConstraintViolationException extends HttpException
{
    private \Symfony\Component\Validator\ConstraintViolationListInterface $errors;

    public function __construct(\Symfony\Component\Validator\ConstraintViolationListInterface $errors, ?\Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct(400, 'Certains champs sont manquants ou malformés', $previous);
    }

    public function getErrors(): \Symfony\Component\Validator\ConstraintViolationListInterface
    {
        return $this->errors;
    }

    public function getErrorMessages(): array
    {
        $messages = [];
        foreach ($this->errors as $error) {
            $messages[] = $error->getMessage();
        }
        return $messages;
    }
}