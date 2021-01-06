<?php

namespace PDIC;

class ExceptionNotFound extends Exception implements \Psr\Container\NotFoundExceptionInterface
{

    public function __construct($message = null, $code = 0)
    {
        $this->message = sprintf('class "%s" not found', $message);
        $this->code = $code;
    }

}
