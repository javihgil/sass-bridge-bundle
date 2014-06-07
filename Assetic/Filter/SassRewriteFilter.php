<?php
namespace Jhg\SassBridgeBundle\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;
use Jhg\SassBridgeBundle\Assetic\Utils\SassUtils;

/**
 * Class SassRewriteFilter
 * @package Jhg\SassBridgeBundle\Assetic\Filter
 * @author Javi H. Gil <javihgil@gmail.com>
 *
 * @see http://github.com/javihgil/assetic-rewritesf-filter-bundle.git
 */
class SassRewriteFilter implements FilterInterface
{
    /**
     * @return \AppCache|\AppKernel
     */
    public function getKernel() {
        global $kernel;
        return $kernel;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer() {
        return $this->getKernel()->getContainer();
    }

    /**
     * Preprocesses a sass asset file
     *
     * @param AssetInterface $asset
     */
    public function filterLoad(AssetInterface $asset)
    {
        $assetSourceBase = $asset->getSourceRoot();
        $assetSourcePath = $asset->getSourcePath();
        $assetAbsolutePath = pathinfo("$assetSourceBase/$assetSourcePath",PATHINFO_DIRNAME);

        $filePath = realpath($assetSourcePath);

        $kernelRootDir = $this->getKernel()->getRootDir();

        $content = $this->rewriteSassContent($asset->getContent(),pathinfo($filePath,PATHINFO_DIRNAME));
        $asset->setContent($content);
    }

    /**
     * @param AssetInterface $asset
     */
    public function filterDump(AssetInterface $asset)
    {

    }


    /**
     * @param $content
     * @param $filePath
     * @return mixed|string
     */
    public function rewriteSassContent($content,$filePath) {
        // replace import contents
        $content = $this->replaceSassImportRelativeReferences($content,$filePath);

        // replace bundle import contents
        $content = $this->replaceSassBundleImportReferences($content);

        // replace configured resources
        $resources = $this->getContainer()->getParameter('sass.resources_paths');
        foreach($resources as $name=>$path) {
            $content = $this->replaceSassResourcesImportReferences($content,$name,$path);
        }

        return $content;
    }


    /*
     * @import '@NameBundle/.../file.sass';
     * @import "@NameBundle/.../file.sass";
     * @import @NameBundle/.../file.sass;
     * @import '@NameBundle/.../file.sass'
     * @import "@NameBundle/.../file.sass"
     * @import @NameBundle/.../file.sass
     *
     * $matches = array( 0=>"@import '@NameBundle/.../file.sass'", 1=>"@NameBundle/.../file.sass", 2=>"/test.sass" );
     */
    const REGEX_IMPORT_BUNDLE_REFERENCE    = '/@import [\'\"]?(@[a-z0-9]+Bundle(\/[a-z0-9\-\_\.]+)+)[\'\"]?;?/i';

    /**
     * @param string $content
     *
     * @return string
     */
    public function replaceSassBundleImportReferences($content) {
        $kernel = $this->getKernel();
        $filter = $this;

        $callback = function($matches) use ($kernel,$filter) {
            // obtains resource using bundle referenced order (app/Resources, bundle)
            $resource_path = $kernel->locateResource($matches[1],$kernel->getRootDir().'/Resources');

            // get bundle resource content
            $resource_content = file_get_contents($resource_path);

            // rewrite resource content recursively
            $resource_content = $filter->rewriteSassContent($resource_content,pathinfo($resource_path,PATHINFO_DIRNAME));

            // rewrites matches elements
            return str_replace($matches[0],$resource_content,$matches[0]);
        };

        $limit = -1;
        $count = 0;
        // replace all references
        return preg_replace_callback(static::REGEX_IMPORT_BUNDLE_REFERENCE, $callback, $content, $limit, $count);
    }




    /*
     * Example:
     *
     * jhg_sass_bridge:
     *      resources_paths:
     *          bootstrap: %kernel.root_dir%/../vendor/twbs/bootstrap-sass/vendor/assets/stylesheets/bootstrap
     *
     * @import '@bootstrap/alerts.sass';
     * $matches = array( 0=>"@import '@bootstrap/alerts.sass'", 1=>"@bootstrap/alerts.sass", 2=>"/alerts.sass" );
     */

    /**
     * @param $content
     * @param $name
     * @param $resources_path
     * @return mixed
     */
    public function replaceSassResourcesImportReferences($content,$name,$resources_path) {
        $kernel = $this->getKernel();
        $filter = $this;

        $callback = function($matches) use ($kernel,$filter,$name,$resources_path) {
            // calculate file name
            $resource_clean_name = SassUtils::cleanSassName($matches[2]);

            $pathMatches = array();
            if( preg_match('/@'.$name.'(\/[a-z0-9\-\_\.]+)+\/[a-z0-9\-\_\.]+/i',$matches[1],$pathMatches) ) {
                $resources_path .= '/'.trim($pathMatches[1],'/');
            }

            // obtains resource using bundle referenced order (app/Resources, bundle)
            $resource_path = $this->getSassResourceRealPath($resources_path,$resource_clean_name,$matches[1]);

            // get bundle resource content
            $resource_content = file_get_contents($resource_path);

            // rewrite resource content recursively
            $resource_content = $filter->rewriteSassContent($resource_content,pathinfo($resource_path,PATHINFO_DIRNAME));

            // rewrites matches elements
            return str_replace($matches[0],$resource_content,$matches[0]);
        };

        $regex = '/@import [\'\"]?(@'.$name.'(\/[a-z0-9\-\_\.]+)+)[\'\"]?;?/i';

        $limit = -1;
        $count = 0;
        // replace all references
        return preg_replace_callback($regex, $callback, $content, $limit, $count);
    }


    /*
     * @import 'mixins/alerts';
     *
     * $matches = array( 0=>"@import 'mixins/alerts'", 1=>"mixins/alerts", 2=>"/alerts" );
     */
    const REGEX_IMPORT_RELATIVE_REFERENCE    = '/@import [\'\"]?((\/?[a-z0-9\-\_\.]+)+)[\'\"]?;?/i';

    /**
     * @param $content
     * @param $locationPath
     * @return mixed
     */
    public function replaceSassImportRelativeReferences($content,$locationPath) {
        $kernel = $this->getKernel();
        $filter = $this;

        $callback = function($matches) use ($kernel,$filter,$locationPath) {
            // calculate file name
            $resource_clean_name = SassUtils::cleanSassName($matches[2]);

            $pathMatches = array();
            if( preg_match('/\/?([a-z0-9\-\_\.]+)+\/[a-z0-9\-\_\.]+/i',$matches[1],$pathMatches) ) {
                $locationPath .= '/'.trim($pathMatches[1],'/');
            }

            // obtains resource using bundle referenced order (app/Resources, bundle)
            $resource_path = $this->getSassResourceRealPath($locationPath,$resource_clean_name,$matches[1]);

            // get bundle resource content
            $resource_content = file_get_contents($resource_path);

            // rewrite resource content recursively
            $resource_content = $filter->rewriteSassContent($resource_content,pathinfo($resource_path,PATHINFO_DIRNAME));

            // rewrites matches elements
            return str_replace($matches[0],$resource_content,$matches[0]);
        };

        $limit = -1;
        $count = 0;
        // replace all references
        return preg_replace_callback(static::REGEX_IMPORT_RELATIVE_REFERENCE, $callback, $content, $limit, $count);
    }


    /**
     * This method "overrides" the SassUtils static one, for allow unit testing
     *
     * @param $file_location
     * @param $clean_file_name
     * @param $original_resource_name
     *
     * @return string
     */
    public function getSassResourceRealPath($file_location,$clean_file_name,$original_resource_name) {
        return SassUtils::getSassResourceRealPath($file_location,$clean_file_name,$original_resource_name);
    }

}