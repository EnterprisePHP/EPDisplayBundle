<?php

namespace EP\DisplayBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ep_display');

        $rootNode
            ->children()
                ->arrayNode('global')
                    ->children()
                        ->scalarNode('image_render')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('file_render')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('array_collection_render')
                            ->defaultTrue()
                        ->end()
                        ->integerNode('collection_item_count')
                            ->defaultValue(10)
                        ->end()
                        ->scalarNode('template')
                            ->defaultValue("EPDisplayBundle:display.html.twig")
                        ->end()
                        ->arrayNode('exclude_vars')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function($v) { return preg_split('/\s*,\s*/', $v); })
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
