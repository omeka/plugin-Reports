<?php 
/**
 * Main plugin script
 *
 * Main script for the plugin, sets up hooks and filters to the Omeka API.
 *
 * @package Reports
 * @author Center for History and New Media
 * @copyright Copyright 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

define('REPORTS_PLUGIN_DIRECTORY', dirname(__FILE__));

define('REPORTS_SAVE_DIRECTORY', get_option('reports_save_directory'));

define('REPORTS_GENERATOR_DIRECTORY', REPORTS_PLUGIN_DIRECTORY.'/libraries/Reports/ReportGenerator');

define('REPORTS_GENERATOR_PREFIX', 'Reports_ReportGenerator_');

add_plugin_hook('install', 'reports_install');
add_plugin_hook('uninstall', 'reports_uninstall');
add_plugin_hook('config_form', 'reports_config_form');
add_plugin_hook('config', 'reports_config');
add_plugin_hook('define_routes', 'reports_define_routes');
add_filter('admin_navigation_main', 'reports_admin_navigation_main');

/**
 * install callback
 */
function reports_install()
{
    set_option('reports_plugin_version', get_plugin_ini('Reports', 'version'));
    
    $command = 'which php 2>&0';
    $lastLineOutput = exec($command, $output, $returnVar);
    $phpPath = $returnVar == 0 ? trim($lastLineOutput) : '';
    set_option('reports_php_path', $phpPath);
    
    set_option('reports_save_directory', REPORTS_PLUGIN_DIRECTORY.
                                     '/generated_reports');
    
    $db = get_db();
    
    /* Table: Stores 
       
       id: primary key (also the value of the token)
       verb: Verb of original request
       metadata_prefix: metadataPrefix of original request
       from: Optional from argument of original request
       until: Optional until argument of original request
       set: Optional set argument of original request
       expiration: Datestamp after which token is expired
    */
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}reports_reports` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` TINYTEXT COLLATE utf8_unicode_ci NOT NULL,
        `description` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
        `creator` INT UNSIGNED NOT NULL,
        `modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}reports_queries` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `report_id` INT UNSIGNED NOT NULL,
        `query` TEXT COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY  (`id`),
        INDEX (`report_id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}reports_items` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `report_id` INT UNSIGNED NOT NULL,
        `item_id` INT UNSIGNED NOT NULL,
        PRIMARY KEY  (`id`),
        INDEX (`report_id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}reports_files` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `report_id` INT UNSIGNED NOT NULL,
        `type` TINYTEXT COLLATE utf8_unicode_ci NOT NULL,
        `filename` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
        `status` ENUM('starting', 'in progress', 'completed', 'error') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'starting',
        `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `pid` INT UNSIGNED DEFAULT NULL,
        PRIMARY KEY  (`id`),
        INDEX(`report_id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
}

/**
 * uninstall callback
 */
function reports_uninstall()
{
    delete_option('reports_plugin_version');
    delete_option('reports_php_path');
    delete_option('reports_save_directory');
    
    $db = get_db();
    
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_reports`;";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_queries`;";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_items`;";
    $db->query($sql);
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_files`;";
    $db->query($sql);
}

function reports_config_form()
{
    $phpPath = get_option('reports_php_path');
    $saveDirectory = get_option('reports_save_directory');
    
    include 'config_form.php';
}

function reports_config()
{
    set_option('reports_php_path', $_POST['reports_php_path']);
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

function reports_getNameForEntityId($entityId)
{
    return get_db()->getTable('Entity')->find($entityId)->getName();
}

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