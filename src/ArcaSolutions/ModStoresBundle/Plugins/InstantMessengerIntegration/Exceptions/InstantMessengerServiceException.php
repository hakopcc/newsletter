<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\InstantMessengerIntegration\Exceptions;


use Exception;
use Throwable;

class InstantMessengerServiceException extends Exception
{
    /**
     * InstantMessengerServiceException constructor.
     * @param string $message
     * @param Throwable $previous
     * @param int $code
     */
    public function __construct(string $message, Throwable $previous, $code = 0)
    {
        parent::__construct($message, $code, $previous);
    }
}
