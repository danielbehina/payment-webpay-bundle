<?php

namespace JakubZapletal\Payment\WebpayBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class JakubZapletalPaymentWebpayExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter(sprintf('%s.bank_name', $this->getAlias()), $config['bank_name']);
        $container->setParameter(sprintf('%s.merchant_number', $this->getAlias()), $config['merchant_number']);
        $container->setParameter(sprintf('%s.private_key_path', $this->getAlias()), $config['private_key_path']);
        $container->setParameter(sprintf('%s.private_key_password', $this->getAlias()), $config['private_key_password']);
        $container->setParameter(sprintf('%s.debug', $this->getAlias()), $config['debug']);
        $container->setParameter(sprintf('%s.muzo_key_path', $this->getAlias()), $config['muzo_key_path']);
    }


}
