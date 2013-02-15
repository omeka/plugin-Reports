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
 * Converts the advanced search output into acceptable input for findBy().
 *
 * @see Omeka_Db_Table::findBy()
 * @param array $query HTTP query string array
 * @return array Array of findBy() parameters
 */
function reports_convert_search_filters($query) {
    $perms  = array();
    $filter = array();
    $order  = array();
    
    //Show only public items
    if ($query['public']) {
        $perms['public'] = true;
    }
    
    //Here we add some filtering for the request    
    // User-specific item browsing
    if ($userToView = $query['user']) {
        if (is_numeric($userToView)) {
            $filter['user'] = $userToView;
        }
    }

    if ($query['featured']) {
        $filter['featured'] = true;
    }
    
    if ($collection = $query['collection']) {
        $filter['collection'] = $collection;
    }
    
    if ($type = $query['type']) {
        $filter['type'] = $type;
    }
    
    if (($tag = @$query['tag']) || ($tag = @$query['tags'])) {
        $filter['tags'] = $tag;
    }
    
    if ($excludeTags = @$query['excludeTags']) {
        $filter['excludeTags'] = $excludeTags;
    }
    
    if ($search = $query['search']) {
        $filter['search'] = $search;
    }
    
    //The advanced or 'itunes' search
    if ($advanced = $query['advanced']) {
        
        //We need to filter out the empty entries if any were provided
        foreach ($advanced as $k => $entry) {                    
            if (empty($entry['element_id']) || empty($entry['type'])) {
                unset($advanced[$k]);
            }
        }
        $filter['advanced_search'] = $advanced;
    };
    
    if ($range = $query['range']) {
        $filter['range'] = $range;
    }
        
    return array_merge($perms, $filter, $order);
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
        if ($config->Reports) {
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