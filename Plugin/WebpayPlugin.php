<?php

namespace JakubZapletal\Payment\WebpayBundle\Plugin;

use JakubZapletal\Payment\WebpayBundle\Client\RequestClient;
use JakubZapletal\Payment\WebpayBundle\Client\ResponseClient;
use JMS\Payment\CoreBundle\Plugin\AbstractPlugin;
use JMS\Payment\CoreBundle\Model\FinancialTransactionInterface;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;
use JMS\Payment\CoreBundle\Plugin\Exception\Action\VisitUrl;
use JMS\Payment\CoreBundle\Plugin\Exception\ActionRequiredException;
use JMS\Payment\CoreBundle\Plugin\Exception\PaymentPendingException;
use JMS\Payment\CoreBundle\Plugin\PluginInterface;

class WebpayPlugin extends AbstractPlugin
{
    /**
     * @var \JakubZapletal\Payment\WebpayBundle\Client\RequestClient
     */
    protected $requestClient;

    /**
     * @var \JakubZapletal\Payment\WebpayBundle\Client\ResponseClient
     */
    protected $responseClient;

    public function __construct(RequestClient $requestClient, ResponseClient $responseClient)
    {
        $this->requestClient = $requestClient;
        $this->responseClient = $responseClient;
    }

    function approveAndDeposit(FinancialTransactionInterface $transaction, $retry)
    {
        try {
            $this->responseClient->isValid();

            $transaction->setReferenceNumber($this->responseClient->getOrderNumber());
            $transaction->setProcessedAmount($transaction->getPayment()->getApprovingAmount());
            $transaction->setResponseCode(PluginInterface::RESPONSE_CODE_SUCCESS);
            $transaction->setReasonCode(PluginInterface::REASON_CODE_SUCCESS);
        } catch (ActionRequiredException $e) {
            $actionRequest = new ActionRequiredException('User has not yet authorized the transaction.');
            $actionRequest->setFinancialTransaction($transaction);
            $actionRequest->setAction(new VisitUrl($this->getRedirectUrl($transaction)));

            throw $actionRequest;
        }
    }

    protected function getRedirectUrl(FinancialTransactionInterface $transaction)
    {
        $data = $transaction->getExtendedData();

        $client = $this->requestClient;

        $client
            ->setAmount($transaction->getPayment()->getApprovingAmount())
            ->setCurrency($transaction->getPayment()->getPaymentInstruction()->getCurrency())
            ->setOrderNumber($transaction->getPayment()->getId())
            ->setReturnUrl($data->get('return_url'))
        ;

        if ($data->has('description')) {
            $client->setDescription($data->get('description'));
        }

        if ($data->has('merchantOrderNumber')) {
            $client->setMerchantOrderNumber($data->get('merchantOrderNumber'));
        }

        return $client->getRequestUrl();
    }

    public function processes($name)
    {
        return 'webpay' === $name;
    }
}