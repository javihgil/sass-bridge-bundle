<?php
namespace Jhg\SassBridgeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Jhg\SassBridgeBundle\DependencyInjection
 * @author Javi H. Gil <javihgil@gmail.com>
 */
class Configuration implements ConfigurationInterface {
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('jhg_sass_bridge');

        $rootNode
            ->children()
                ->arrayNode('resources_paths')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end() // arrayNode('resources')
            ->end();

        return $treeBuilder;
    }
}