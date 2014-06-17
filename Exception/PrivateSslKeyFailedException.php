<?php

namespace JakubZapletal\Payment\WebpayBundle\Exception;

use Symfony\Component\PropertyAccess\Exception\RuntimeException;

/**
 * Thrown when getting private ssl key failed due to bad combination of key and passphrase.
 */
class PrivateSslKeyFailedException extends RuntimeException
{
}