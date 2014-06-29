# Payment Webpay Bundle

[![Total Downloads](https://poser.pugx.org/jakubzapletal/payment-webpay-bundle/downloads.png)](https://packagist.org/packages/jakubzapletal/payment-webpay-bundle)
[![Latest Unstable Version](https://poser.pugx.org/jakubzapletal/payment-webpay-bundle/v/unstable.png)](https://packagist.org/packages/jakubzapletal/payment-webpay-bundle)
[![License](https://poser.pugx.org/jakubzapletal/payment-webpay-bundle/license.png)](https://packagist.org/packages/jakubzapletal/payment-webpay-bundle)

This is an extension to the [JMSPaymentCoreBundle](http://jmsyst.com/bundles/JMSPaymentCoreBundle) providing access to the GP Webpay API ([https://www.globalpaymentsinc.com](https://www.globalpaymentsinc.com)).

**Available Transaction Types:**
* approveAndDeposit

## Instalation

### Composer

If you don't have Composer [install](http://getcomposer.org/doc/00-intro.md#installation) it:

```bash
$ curl -s https://getcomposer.org/installer | php
```

Add `jakubzapletal/payment-webpay-bundle` to `composer.json`:

```bash
$ composer require "jakubzapletal/payment-webpay-bundle:1.0.*@dev"
```

### Register a bundle

```php
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new JakubZapletal\Payment\WebpayBundle\JakubZapletalPaymentWebpayBundle(),
    // ...
);
```

### Dependencies

This plugin depends on the [JMSPaymentCoreBundle](https://github.com/schmittjoh/JMSPaymentCoreBundle/blob/master/Resources/doc/index.rst), so you'll need to add this to your kernel as well even if you don't want to use its persistence capabilities.


### Configuration

```yaml
jakub_zapletal_payment_webpay:
    bank_name: short name of your bank # example 'rb'
    merchant_number: your merchant number obtained from GP webpay or your bank
    private_key_path: absolute path to your private key *.pem file # example '%kernel.root_dir%/Resources/private_key.pem'
    private_key_password: password to your private key
    muzo_key_path: absolute path to muzo key *.pem file # example '%kernel.root_dir%/Resource/muzo_prod.pem'
    debug: true/false # when true, connect to Webpay test; uses kernel debug value when not specified

```

## Usage

### With the Payment Plugin Controller (Recommended)

Example is inspired by official JMSPaymentCorebundle usage. There is shown only a different part than the official one.

```php
// class PaymentController

    // ...

    /**
     * @Route("/{orderNumber}/details", name = "payment_details")
     * @Template
     */
    public function detailsAction(Order $order)
    {
        $form = $this->getFormFactory()->create('jms_choose_payment_method', null, array(
            'amount'   => $order->getAmount(),
            'currency' => 'EUR',
            'default_method' => 'payment_webpay', // Optional
            'predefined_data' => array(
                'webpay' => array(
                    'return_url' => $this->router->generate('payment_complete', array(
                        'orderNumber' => $order->getOrderNumber(),
                    ), true),
                    'merchantOrderNumber' => $order->getId(), // Optional
                    'description' => (string)$order->getProduct() // Optional
                )
            ),
        ));

        if ('POST' === $this->request->getMethod()) {
            $form->bindRequest($this->request);

            if ($form->isValid()) {
                $this->ppc->createPaymentInstruction($instruction = $form->getData());

                $order->setPaymentInstruction($instruction);
                $this->em->persist($order);
                $this->em->flush($order);

                return new RedirectResponse($this->router->generate('payment_complete', array(
                    'orderNumber' => $order->getOrderNumber(),
                )));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    // ...
```


### Without the Payment Plugin Controller

The Payment Plugin Controller is made available by the CoreBundle and basically is the interface to a persistence backend 
like the Doctrine ORM. It also performs additional integrity checks to validate transactions. If you don’t need these checks, 
and only want an easy way to communicate with the Webpay API, then you can use the plugin directly:

```php
$plugin = $container->get('jakub_zapletal.payment.webpay.plugin.webpay');
```

## Contributing

Contributions are welcome! Please see the [Contribution Guidelines](contributing.md).