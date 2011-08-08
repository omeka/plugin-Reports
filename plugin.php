<?php 
/**
 * Main plugin script
 *
 * Main script for the plugin, sets up hooks and filters to the Omeka API.
 *
 * TODO: Status indicator on browse page.
 * TODO: Reports should be stored using Omeka_Storage.
 * @package Reports
 * @author Center for History and New Media
 * @copyright Copyright 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

define('REPORTS_PLUGIN_DIRECTORY', dirname(__FILE__));

define('REPORTS_GENERATOR_DIRECTORY', REPORTS_PLUGIN_DIRECTORY .
                                      '/models/Reports/Generator');

add_plugin_hook('install', 'reports_install');
add_plugin_hook('uninstall', 'reports_uninstall');
add_plugin_hook('define_acl', 'reports_define_acl');
add_filter('admin_navigation_main', 'reports_admin_navigation_main');

/**
 * Installs the plugin, setting up options and tables.
 */
function reports_install()
{
    $db = get_db();
    
    /* Table: reports_reports
       
       id: Primary key 
       name: Name of report
       description: Description of report
       query: Filter for items
       creator: User ID of creator
       modified: Date report was last modified
    */
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}reports` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` TINYTEXT COLLATE utf8_unicode_ci NOT NULL,
        `description` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
        `query` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
        `creator` INT UNSIGNED NOT NULL,
        `modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
    
    /* Table: reports_items
       
       id: Primary key
       report_id: Link to reports_reports table
       item_id: ID of item to specifically add
    */
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}reports_items` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `report_id` INT UNSIGNED NOT NULL,
        `item_id` INT UNSIGNED NOT NULL,
        PRIMARY KEY  (`id`),
        INDEX (`report_id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
    
    /* Table: reports_files
       
       id: Primary key
       report_id: Link to reports_reports table
       type: Class name of report generator
       filename: Filename of generated report
       status: Status of generation (starting, in progress, completed, error)
       messages: Status messages from generation process
       created: Date report was generated
       options: Extra options to pass to generator
    */
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}reports_files` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `report_id` INT UNSIGNED NOT NULL,
        `type` TINYTEXT COLLATE utf8_unicode_ci NOT NULL,
        `filename` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
        `status` ENUM('starting', 'in progress', 'completed', 'error') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'starting',
        `messages` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
        `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `options` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY  (`id`),
        INDEX(`report_id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
}

/**
 * Uninstalls the plugin, removing all options and tables.
 */
function reports_uninstall()
{
    $db = get_db();
    
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_reports`;";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_items`;";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_files`;";
    $db->query($sql);
}

/**
 * admin_navigation_main filter
 * @param array $tabs array of admin navigation tabs
 */
function reports_admin_navigation_main($tabs)
{
    if (has_permission('Reports_Index', 'index')) {
        $tabs['Reports'] = uri(
            array(
                'controller' => 'index',
                'module' => 'reports',
                'action' => 'index',
            ),
            'default'
        );
    }
    return $tabs;
}

/**
 * Defines the ACL for the reports controllers.
 *
 * @param Omeka_Acl $acl Access control list
 */
function reports_define_acl($acl)
{
    $acl->loadResourceList(array('Reports_Index' => array('add',
                                                          'browse',
                                                          'query',
                                                          'show',
                                                          'generate',
                                                          'delete')));
    $acl->loadResourceList(array('Reports_Files' => array('show',
                                                          'delete')));
}

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

function reports_save_directory()
{
    $default = sys_get_temp_dir();
    $config = Omeka_Context::getInstance()->config->plugins;
    if (!$config || !$config->Reports || !$config->Reports->saveDirectory) {
        return $default;
    }
    return realpath($config->Reports->saveDirectory);
}

function reports_get_storage_prefix()
{
    $default = 'reports/';
    $config = Omeka_Context::getInstance()->config->plugins;
    if (!$config || !$config->Reports || !$config->Reports->storagePrefix) {
        return $default;
    }
    return (string)$config->Reports->storagePrefix;
}
