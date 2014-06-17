<?php

namespace JakubZapletal\Payment\WebpayBundle\Exception;

use Symfony\Component\PropertyAccess\Exception\RuntimeException;

/**
 * Thrown when required property is not set.
 */
class RequiredPropertyException extends RuntimeException
{
}