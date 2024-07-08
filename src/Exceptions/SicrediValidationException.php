<?php

namespace crtlmota\BancoSicrediConnector\Exceptions;

class SicrediValidationException extends \Exception
{
    public function __construct(array $erros)
    {
        parent::__construct(json_encode($erros), 422);
    }
}
