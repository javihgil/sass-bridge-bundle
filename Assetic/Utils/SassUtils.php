<?php
namespace Jhg\SassBridgeBundle\Assetic\Utils;

/**
 * Class SassUtils
 * @package Jhg\SassBridgeBundle\Assetic\Utils
 * @author Javi H. Gil <javihgil@gmail.com>
 */
class SassUtils {
    /**
     * @param $sassFileName
     *
     * @return string
     */
    public static function cleanSassName($sassFileName) {
        $sassFileName = pathinfo($sassFileName,PATHINFO_FILENAME);
        $sassFileName = str_ireplace('.scss','',$sassFileName);
        return trim($sassFileName,'_');
    }


    /**
     * @param $file_location
     * @param $clean_file_name
     * @param $original_resource_name
     *
     * @return string
     * @throws \Exception
     */
    public static function getSassResourceRealPath($file_location,$clean_file_name,$original_resource_name) {
        // try with "file.scss"
        if(file_exists($file_path = "$file_location/{$clean_file_name}.scss")) {
            return $file_path;
        }

        // try with "_file.scss"
        if(file_exists($file_path = "$file_location/_{$clean_file_name}.scss")) {
            return $file_path;
        }

        throw new \Exception("Can not import $original_resource_name. '$file_path' file not found in any sass filename type: '{$clean_file_name}.scss', '_{$clean_file_name}.scss'");
    }
}