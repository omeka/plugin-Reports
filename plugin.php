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

/** Plugin version: will be stored as an option */
define('REPORTS_PLUGIN_VERSION', get_plugin_ini('Reports', 'version'));

define('REPORTS_PLUGIN_DIRECTORY', dirname(__FILE__));

define('REPORTS_SAVE_DIRECTORY', REPORTS_PLUGIN_DIRECTORY.
                                 '/generated_reports/');

add_plugin_hook('install', 'reports_install');
add_plugin_hook('config_form', 'reports_config_form');
add_plugin_hook('config', 'reports_config');
add_plugin_hook('uninstall', 'reports_uninstall');
add_plugin_hook('define_routes', 'reports_define_routes');
add_filter('admin_navigation_main', 'reports_admin_navigation_main');

/**
 * install callback
 */
function reports_install()
{
    set_option('reports_plugin_version', REPORTS_PLUGIN_VERSION);
    
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
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` TINYTEXT COLLATE utf8_unicode_ci NOT NULL,
        `description` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
        `creator` INT(10) UNSIGNED NOT NULL,
        `modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}reports_queries` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `report_id` INT(10) UNSIGNED NOT NULL,
        `query` TEXT COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY  (`id`),
        INDEX (`report_id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}reports_items` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `report_id` INT(10) UNSIGNED NOT NULL,
        `item_id` INT(10) UNSIGNED NOT NULL,
        PRIMARY KEY  (`id`),
        INDEX (`report_id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}reports_files` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `report_id` INT(10) UNSIGNED NOT NULL,
        `type` TINYTEXT COLLATE utf8_unicode_ci NOT NULL,
        `path` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
        `status` ENUM('starting', 'in progress', 'completed', 'error') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'starting',
        `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (`id`),
        INDEX(`report_id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
}

/**
 * config_form callback
 */
function reports_config_form()
{
  /*  $repoName = get_option('oaipmh_repository_name');
    $namespaceID = get_option('oaipmh_repository_namespace_id');
    $listLimit = get_option('oaipmh_repository_list_limit');
    $expirationTime = get_option('oaipmh_repository_expiration_time');
    $exposeFiles = get_option('oaipmh_repository_expose_files');
    include('config_form.php');*/
}

/**
 * config callback
 */ 
function reports_config()
{
  /*set_option('oaipmh_repository_name', $_POST['oaipmh_repository_name']);
    set_option('oaipmh_repository_namespace_id', $_POST['oaipmh_repository_namespace_id']);
    set_option('oaipmh_repository_list_limit', $_POST['oaipmh_repository_list_limit']);
    set_option('oaipmh_repository_expiration_time', $_POST['oaipmh_repository_expiration_time']);
    set_option('oaipmh_repository_expose_files', $_POST['oaipmh_repository_expose_files']);*/
}

/**
 * uninstall callback
 */
function reports_uninstall()
{
  /*  delete_option('oaipmh_repository_plugin_version');
    delete_option('oaipmh_repository_name');
    delete_option('oaipmh_repository_namespace_id');
    delete_option('oaipmh_repository_record_limit');
    delete_option('oaipmh_repository_expiration_time');
    delete_option('oaipmh_repository_expose_files');
    */
    delete_option('reports_plugin_version');
    
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