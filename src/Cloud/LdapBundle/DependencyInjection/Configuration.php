<?php

namespace Cloud\LdapBundle\DependencyInjection;

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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cloud_ldap');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode->children()
                ->arrayNode('main')
                    ->children()
                        ->scalarNode('data_object')->end()
                    ->end()
                ->end()
                ->arrayNode('services')
                    ->prototype('array')
                        ->children()
                            ->booleanNode('enable')
                                ->defaultFalse()
                            ->end()
                            ->booleanNode('default')
                                ->defaultFalse()
                            ->end()
                            ->scalarNode('type')->end()
                            ->scalarNode('data_object')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
