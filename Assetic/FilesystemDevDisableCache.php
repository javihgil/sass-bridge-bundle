<?php
namespace Jhg\SassBridgeBundle\Assetic\Cache;

use Assetic\Cache\FilesystemCache as BaseFilesystemCache;

/**
 * Class FilesystemDevDisableCache
 * @package Jhg\SassBridgeBundle\Assetic\Cache
 * @author Javi H. Gil <javihgil@gmail.com>
 */
class FilesystemDevDisableCache extends BaseFilesystemCache {
    public function has($key) {
        return false;
    }
}