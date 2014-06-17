<?php

namespace JakubZapletal\Payment\WebpayBundle\Client;

use JakubZapletal\Payment\WebpayBundle\Exception\PrivateSslKeyFailedException;
use JakubZapletal\Payment\WebpayBundle\Exception\RequiredPropertyException;
use Alcohol\ISO4217;

class RequestClient
{
    /**
     * Url path to Webpay API
     *
     * @var string
     */
    protected $apiUrl = 'https://3dsecure.gpwebpay.com/%s/order.do';

    /**
     * Url path to test Webpay API
     *
     * @var string
     */
    protected $apiUrlTest = 'https://test.3dsecure.gpwebpay.com/%s/order.do';

    /**
     * @var bool
     */
    protected $debug = true;

    /**
     * @var string
     */
    protected $bankName;

    /**
     * @var string
     */
    protected $privateKeyPath;

    /**
     * @var string
     */
    protected $privateKeyPassword;

    /**
     * @var int
     */
    protected $merchantNumber;

    /**
     * @var string
     */
    protected $operation = 'CREATE_ORDER';

    /**
     * @var int
     */
    protected $orderNumber;

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var int
     */
    protected $currency;

    /**
     * @var int
     */
    protected $depositFlag = 1;

    /**
     * @var int
     */
    protected $merchantOrderNumber;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $returnUrl;

    /**
     * @param string $bankName
     * @param string $merchantNumber
     * @param string $privateKeyPath
     * @param string $privateKeyPassword
     * @param bool $debug
     */
    public function __construct($bankName, $merchantNumber, $privateKeyPath, $privateKeyPassword, $debug)
    {
        $this->bankName           = $bankName;
        $this->merchantNumber     = $merchantNumber;
        $this->privateKeyPath     = $privateKeyPath;
        $this->privateKeyPassword = $privateKeyPassword;
        $this->debug              = $debug;
    }

    /**
     * Get composed url for request to Webpay API
     *
     * @return string
     */
    public function getRequestUrl()
    {
        $params = $this->getParams();

        $privateKey = $this->getPrivateKey();

        $params['DIGEST'] = $this->getEncodedDigest($params, $privateKey);

        if ($this->bankName === null) {
            throw new RequiredPropertyException('\'bankName\' is not set');
        }

        if ($this->debug) {
            $url = sprintf($this->apiUrlTest, $this->bankName) . '?' . http_build_query ($params);
        } else {
            $url = sprintf($this->apiUrl, $this->bankName) . '?' . http_build_query ($params);
        }

        return $url;
    }

    /**
     * Get params for request
     *
     * @return array
     */
    protected function getParams()
    {
        $params = [];

        if ($this->merchantNumber === null) {
            throw new RequiredPropertyException('\'merchantNumber\' is not set');
        }
        $params['MERCHANTNUMBER'] = $this->merchantNumber;

        $params['OPERATION'] = $this->operation;

        if ($this->orderNumber === null) {
            throw new RequiredPropertyException('\'orderNumber\' is not set');
        }
        $params['ORDERNUMBER'] = $this->orderNumber;

        if ($this->amount === null) {
            throw new RequiredPropertyException('\'amount\' is not set');
        }
        $params['AMOUNT'] = $this->amount;

        if ($this->currency === null) {
            throw new RequiredPropertyException('\'currency\' is not set');
        }
        $params['CURRENCY'] = $this->currency;

        $params['DEPOSITFLAG'] = $this->depositFlag;

        if ($this->merchantOrderNumber !== null) {
            $params ['MERORDERNUM'] = $this->merchantOrderNumber;
        }

        if ($this->returnUrl === null) {
            throw new RequiredPropertyException('\'returnUrl\' is not set');
        }
        $params['URL'] = $this->returnUrl;

        if ($this->description !== null) {
            $params['DESCRIPTION'] = $this->description;
        }

        return $params;
    }

    /**
     * Get private key
     *
     * @return resource
     */
    protected function getPrivateKey()
    {
        if ($this->privateKeyPath === null) {
            throw new RequiredPropertyException('\'privateKeyPath\' is not set');
        }

        $fp = fopen($this->privateKeyPath, "r");
        $key = fread($fp, filesize($this->privateKeyPath));

        fclose ($fp);

        if ($this->privateKeyPassword === null) {
            throw new RequiredPropertyException('\'privateKeyPassword\' is not set');
        }

        $privateKey = openssl_pkey_get_private($key, $this->privateKeyPassword);

        if (!$privateKey) {
            throw new PrivateSslKeyFailedException();
        }

        return $privateKey;
    }

    /**
     * Get encoded digest from params by private key
     *
     * @param array $params
     * @param resource $privateKey
     *
     * @return string
     */
    protected function getEncodedDigest($params, $privateKey)
    {
        $digestText = implode ('|', $params);

        openssl_sign($digestText, $signature, $privateKey);

        $signature = base64_encode ($signature);

        return $signature;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount * 100;

        return $this;
    }

    /**
     * @param int $currency
     */
    public function setCurrency($currency)
    {
        $currency = ISO4217::getByAlpha3($currency);

        $this->currency = $currency['numeric'];

        return $this;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param int $merchantOrderNumber
     */
    public function setMerchantOrderNumber($merchantOrderNumber)
    {
        if ($this->bankName == 'rb') {
            $merchantOrderNumber = substr($merchantOrderNumber, 0, 10);
        } elseif ($this->bankName == 'kb') {
            $merchantOrderNumber = substr($merchantOrderNumber, 0, 16);
        }

        $this->merchantOrderNumber = $merchantOrderNumber;

        return $this;
    }

    /**
     * @param int $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * @param string $privateKeyPassword
     */
    public function setPrivateKeyPassword($privateKeyPassword)
    {
        $this->privateKeyPassword = $privateKeyPassword;

        return $this;
    }

    /**
     * @param string $privateKeyPath
     */
    public function setPrivateKeyPath($privateKeyPath)
    {
        $this->privateKeyPath = $privateKeyPath;

        return $this;
    }

    /**
     * @param string $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }


} 