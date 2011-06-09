<?php 
/**
 * Main plugin script
 *
 * Main script for the plugin, sets up hooks and filters to the Omeka API.
 *
 * TODO: Convert to Omeka_Job
 * FIXME: Remove all unnecessaries.  
 * @package Reports
 * @author Center for History and New Media
 * @copyright Copyright 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

define('REPORTS_PLUGIN_DIRECTORY', dirname(__FILE__));

define('REPORTS_SAVE_DIRECTORY', get_option('reports_save_directory'));

define('REPORTS_GENERATOR_DIRECTORY', REPORTS_PLUGIN_DIRECTORY .
                                      '/models/Reports/Generator');

define('REPORTS_GENERATOR_PREFIX', 'Reports_Generator_');

add_plugin_hook('install', 'reports_install');
add_plugin_hook('uninstall', 'reports_uninstall');
add_plugin_hook('config_form', 'reports_config_form');
add_plugin_hook('config', 'reports_config');
add_plugin_hook('define_routes', 'reports_define_routes');
add_plugin_hook('define_acl', 'reports_define_acl');
add_filter('admin_navigation_main', 'reports_admin_navigation_main');

/**
 * Installs the plugin, setting up options and tables.
 */
function reports_install()
{
    set_option('reports_plugin_version', get_plugin_ini('Reports', 'version'));
    
    set_option('reports_save_directory', REPORTS_PLUGIN_DIRECTORY.
                                         '/'.
                                         'generated_reports');
    
    $db = get_db();
    
    /* Table: reports_reports
       
       id: Primary key 
       name: Name of report
       description: Description of report
       query: Filter for items
       creator: Entity ID of creator
       modified: Date report was last modified
    */
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}reports_reports` (
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
       pid: Process ID for background script
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
        `pid` INT UNSIGNED DEFAULT NULL,
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
    delete_option('reports_plugin_version');
    delete_option('reports_save_directory');
    
    $db = get_db();
    
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_reports`;";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_items`;";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_files`;";
    $db->query($sql);
}

/**
 * Shows the configuration form.
 */
function reports_config_form()
{
    $saveDirectory = get_option('reports_save_directory');
    
    include 'config_form.php';
}

/**
 * Processes the configuration form.
 */
function reports_config()
{
    set_option('reports_save_directory', $_POST['reports_save_directory']);
}

/**
 * admin_navigation_main filter
 * @param array $tabs array of admin navigation tabs
 */
function reports_admin_navigation_main($tabs)
{
    $tabs['Reports'] = uri('reports');
    return $tabs;
}

/**
 * Defines custom routes for the reports controllers.
 * @param Zend_Controller_Router_Interface $router Router
 */
function reports_define_routes($router)
{
    $router->addRoute('reports-sub-controllers',
                      new Zend_Controller_Router_Route(
                          'reports/:controller/:action/:id',
                          array( 'module'     => 'reports'),
                          array( 'id'         => '\d+')));
    $router->addRoute('reports-action', 
                      new Zend_Controller_Router_Route(
                          'reports/:action',
                          array( 'module'     => 'reports',
                                 'controller' => 'index')));
    $router->addRoute('reports-id-action', 
                      new Zend_Controller_Router_Route(
                          'reports/:action/:id',
                          array( 'module'     => 'reports',
                                 'controller' => 'index'),
                          array( 'id'         => '\d+')));
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
 * Gets the full name associated with the given entity.
 *
 * @param int $entityId Entity ID
 * @return string Full name of entity
 */
function reports_getNameForEntityId($entityId)
{
    return get_db()->getTable('Entity')->find($entityId)->getName();
}

/**
 * Gets all the avaliable output formats.
 *
 * @return array Array in format className => readableName
 */
function reports_getOutputFormats()
{
    $dir = new DirectoryIterator(REPORTS_GENERATOR_DIRECTORY);
    $formats = array();
    foreach ($dir as $entry) {
        if ($entry->isFile() && !$entry->isDot()) {
            $filename = $entry->getFilename();
            if(preg_match('/^(.+)\.php$/', $filename, $match) && $match[1] != 'Abstract') {
                // Get and set only the name of the file minus the extension.
                //require_once($pathname);
                $class = REPORTS_GENERATOR_PREFIX."${match[1]}";
                $object = new $class(null);
                $name = $object->getReadableName();
                $formats[$match[1]] = $name;
            }
        }
    }
    return $formats;
}

/**
 * Converts the advanced search output into acceptable input for findBy().
 *
 * @see Omeka_Db_Table::findBy()
 * @param array $query HTTP query string array
 * @return array Array of findBy() parameters
 */
function reports_convertSearchFilters($query) {
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
    
    if (($tag = $query['tag']) || ($tag = $query['tags'])) {
        $filter['tags'] = $tag;
    }
    
    if ($excludeTags = $query['excludeTags']) {
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
