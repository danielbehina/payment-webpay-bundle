<?php

namespace JakubZapletal\Payment\WebpayBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class WebpayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function getName()
    {
        return 'webpay';
    }
}