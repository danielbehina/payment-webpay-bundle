<?php

namespace JakubZapletal\Payment\WebpayBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('jakub_zapletal_payment_webpay');

        $rootNode
            ->children()
                ->scalarNode('bank_name')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('merchant_number')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('private_key_path')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('private_key_password')->isRequired()->cannotBeEmpty()->end()
                ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                ->scalarNode('muzo_key_path')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
