<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ConstraintViolationException extends HttpException
{
    private \Symfony\Component\Validator\ConstraintViolationListInterface $errors;

    public function __construct(\Symfony\Component\Validator\ConstraintViolationListInterface $errors, ?\Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct(400, 'Validation failed', $previous);
    }

    public function getErrors(): \Symfony\Component\Validator\ConstraintViolationListInterface
    {
        return $this->errors;
    }
}