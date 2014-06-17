# Payment Webpay Bundle

[![Total Downloads](https://poser.pugx.org/jakubzapletal/payment-webpay-bundle/downloads.png)](https://packagist.org/packages/jakubzapletal/payment-webpay-bundle)
[![Latest Unstable Version](https://poser.pugx.org/jakubzapletal/payment-webpay-bundle/v/unstable.png)](https://packagist.org/packages/jakubzapletal/payment-webpay-bundle)
[![License](https://poser.pugx.org/jakubzapletal/payment-webpay-bundle/license.png)](https://packagist.org/packages/jakubzapletal/payment-webpay-bundle)


## Instalation


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