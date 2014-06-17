<?php

namespace JakubZapletal\Payment\WebpayBundle\Client;

use JakubZapletal\Payment\WebpayBundle\Exception\RequiredPropertyException;
use JakubZapletal\Payment\WebpayBundle\Exception\PrivateSslKeyFailedException;
use JMS\Payment\CoreBundle\Exception\Exception;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\BlockedException;
use JMS\Payment\CoreBundle\Plugin\Exception\CommunicationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use JMS\Payment\CoreBundle\Plugin\Exception\InternalErrorException;
use JMS\Payment\CoreBundle\Plugin\Exception\FinancialException;
use JMS\Payment\CoreBundle\Plugin\Exception\InvalidDataException;
use JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException;
use JMS\Payment\CoreBundle\Plugin\Exception\TimeoutException;

class ResponseClient
{
    /**
     * @var string
     */
    protected $muzoKeyPath;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var string
     */
    protected $digest;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param $muzoKeyPath
     */
    public function __construct($muzoKeyPath)
    {
        $this->muzoKeyPath = $muzoKeyPath;
    }


    public function setRequest(Request $request = null)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Validate response
     *
     * @return bool
     */
    public function isValid()
    {
        $this->setResponseParams($this->request->query);

        $result = openssl_verify($this->getEncodedParams(), $this->getDecodedDigest(), $this->getMuzoKey());

        if ($result === 1) {
            if ($this->params['PRCODE'] == 0 AND $this->params['SRCODE'] == 0) {
                return true;
            } else {
                $exception = $this->getException((int)$this->params['PRCODE']);

                throw $exception;
            }
        } elseif ($result === 0) {
            throw new InvalidDataException('Signature is incorrect.');
        } else {
            throw new InternalErrorException('Error during signature verification.');
        }
    }

    /**
     * Get order number
     *
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->params['ORDERNUMBER'];
    }

    /**
     * Set data from response
     *
     * @param ParameterBag $params
     *
     * @return $this
     */
    protected function setResponseParams(ParameterBag $params)
    {
        $responseParams = [];

        $responseParams['OPERATION']   = $params->get('OPERATION', '');
        $responseParams['ORDERNUMBER'] = $params->get('ORDERNUMBER', '');
        $responseParams['MERORDERNUM'] = $params->get('MERORDERNUM', '');
        $responseParams['PRCODE']      = $params->get('PRCODE', '');
        $responseParams['SRCODE']      = $params->get('SRCODE', '');
        $responseParams['RESULTTEXT']  = $params->get('RESULTTEXT', '');

        $this->params = $responseParams;

        $this->digest = $params->get('DIGEST', null);

        return $this;
    }

    /**
     * Get encoded response params
     *
     * @return string
     */
    protected function getEncodedParams()
    {
        return implode('|', $this->params);
    }

    /**
     * Get decoded response digest
     *
     * @return string
     */
    protected function getDecodedDigest()
    {
        if ($this->digest === null) {
            throw new ActionRequiredException();
        }

        return base64_decode($this->digest);
    }

    /**
     * Get muzo key
     *
     * @return resource
     */
    protected function getMuzoKey()
    {
        if ($this->muzoKeyPath === null) {
            throw new RequiredPropertyException('\'muzoKeyPath\' is not set');
        }

        $fp = fopen($this->muzoKeyPath, "r");
        $key = fread($fp, filesize($this->muzoKeyPath));

        fclose ($fp);

        $muzoKey = openssl_get_publickey($key);

        if (!$muzoKey) {
            throw new PrivateSslKeyFailedException();
        }

        return $muzoKey;
    }

    /**
     * Get exception by PRCODE
     *
     * @param int $prcode
     * @param int $srcode
     *
     * @return Exception|CommunicationException|PaymentPendingException|TimeoutException
     */
    protected function getException($prcode)
    {
        $exceptionText = $this->params['RESULTTEXT'];

        if (in_array($prcode, [1, 2, 3, 4, 5, 11, 14, 27, 31])) {
            return new InvalidDataException($exceptionText);
        } elseif (in_array($prcode, [15, 17, 18, 20, 25, 28, 30])) {
            return new FinancialException($exceptionText);
        } elseif (in_array($prcode, [26, 1000])) {
            return new CommunicationException($exceptionText);
        } elseif (in_array($prcode, [35])) {
            return new TimeoutException($exceptionText);
        } elseif (in_array($prcode, [50])) {
            return new BlockedException($exceptionText);
        } else {
            return new Exception($exceptionText);
        }
    }
} 