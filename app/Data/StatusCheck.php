<?php

namespace App\Data;

class StatusCheck
{
    public bool $status = true;

    public string $message = '';

    public string $code = '';

    public function __construct(bool $status = true, string $message = '', string $code = '')
    {
        $this->status = $status;
        $this->message = $message;
        $this->code = $code;
    }

    public function isStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }
}
