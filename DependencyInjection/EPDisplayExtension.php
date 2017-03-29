<?php

namespace EP\DisplayBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EPDisplayExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->setupBundleConfigs($container, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param ContainerInterface $container
     * @param $configs
     */
    private function setupBundleConfigs(ContainerInterface $container, $configs)
    {
        $globalConfig = isset($configs['global'])? $configs['global']: [];

        //set image_render config as param
        if(isset($globalConfig['image_render'])){
            $container->setParameter('ep_display.config.image_render', $globalConfig['image_render']);
        }else{
            $container->setParameter('ep_display.config.image_render', true);
        }

        //set file_render config as param
        if(isset($globalConfig['file_render'])){
            $container->setParameter('ep_display.config.file_render', $globalConfig['file_render']);
        }else{
            $container->setParameter('ep_display.config.file_render', true);
        }

        //set templating config as param
        if(isset($globalConfig['template'])){
            $container->setParameter('ep_display.config.template', $globalConfig['template']);
        }else{
            $container->setParameter('ep_display.config.template', 'EPDisplayBundle:display.html.twig');
        }

        //set exclude_vars config as param
        if(isset($globalConfig['exclude_vars'])){
            $container->setParameter('ep_display.config.exclude_vars', $globalConfig['exclude_vars']);
        }else{
            $container->setParameter('ep_display.config.exclude_vars', []);
        }

        //set array_collection_render config as param
        if(isset($globalConfig['array_collection_render'])){
            $container->setParameter('ep_display.config.array_collection_render', $globalConfig['array_collection_render']);
        }else{
            $container->setParameter('ep_display.config.array_collection_render', true);
        }

        //set collection_item_count config as param
        if(isset($globalConfig['collection_item_count'])){
            $container->setParameter('ep_display.config.collection_item_count', $globalConfig['collection_item_count']);
        }else{
            $container->setParameter('ep_display.config.collection_item_count', 10);
        }

        return;
    }
}
