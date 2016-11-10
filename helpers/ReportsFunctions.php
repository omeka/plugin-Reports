<?php 
/**
 * Helper functions for Reports plugin
 *
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Gets all the avaliable output formats.
 *
 * @return array Array in format className => readableName
 */
function reports_get_output_formats()
{
    return Reports_Generator::getFormats(REPORTS_GENERATOR_DIRECTORY);
}

/**
 * Returns a configuration setting
 *
 * @param string $key HTTP query string array
 * @param mixed $defaultValue  The default value for the configuration setting if it is not defined elsewhere
 * @return mixed The configuration value  
 */
function reports_get_config($key = null, $defaultValue = null)
{
    $defaults = array(
        'storagePrefixDir' => 'reports',
    );

    $config = Zend_Registry::get('bootstrap')->getResource('Config')->plugins;
    
    // Return the whole config if no key given.
    if (!$key) {
        if ($config) {
            return $defaults + $config->Reports->toArray();
        } else {
            return $defaults;
        }
    }
    if (!$config || !$config->Reports || !$config->Reports->$key) {
        return $defaultValue;
    }
    return $config->Reports->$key;
}
