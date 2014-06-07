<?php
namespace Jhg\SassBridgeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class JhgSassBridgeExtension
 * @package Jhg\SassBridgeBundle\DependencyInjection
 * @author Javi H. Gil <javihgil@gmail.com>
 */
class JhgSassBridgeExtension extends Extension {
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sass.resources_paths',$config['resources_paths']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('assetic/sassrewrite.xml');
    }
}