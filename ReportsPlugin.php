<?php
/**
 * Reports
 * 
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */


defined('REPORTS_PLUGIN_DIRECTORY') or define('REPORTS_PLUGIN_DIRECTORY', dirname(__FILE__));
defined('REPORTS_GENERATOR_DIRECTORY') or define('REPORTS_GENERATOR_DIRECTORY', REPORTS_PLUGIN_DIRECTORY .'/models/Reports/Generator');
require_once(REPORTS_PLUGIN_DIRECTORY . '/helpers/ReportsFunctions.php');

/**
 * The Reports plugin.
 * 
 * @package Omeka\Plugins\Reports
 */
class ReportsPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array('install', 
							  'uninstall', 
							  'define_acl',
							  'define_routes');

    /**
    * @var array Filters for the plugin.
    */
    protected $_filters = array('admin_navigation_main',
                                'search_record_types');
    
    /**
     * Installs the plugin, setting up options and tables.
     */
    public function hookInstall()
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
        
        //IF reports folder doesn't exist create it
        $storage = Zend_Registry::get('storage');
        if($storage instanceof Omeka_Storage_Adapter_Filesystem){
            $options = $storage->getAdapter()->getOptions();
            $localDir = $options['localDir'];
            $reports = $localDir."/reports";
        
            if(file_exists($localDir) and !file_exists($reports)){
                $oldMask = umask(0);
                mkdir($reports, 0775);
                umask($oldMask);
            }
        }
        
    }

    /**
     * Uninstalls the plugin, removing all options and tables.
     */
    public function hookUninstall()
    {
        $db = get_db();

        $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports`;";
        $db->query($sql);
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_items`;";
        $db->query($sql);
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}reports_files`;";
        $db->query($sql);
    }

    /**
     * Add the Reports link to the admin main navigation.
     *
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Reports'),
            //'uri' => url('index'),       
            'controller' => 'index',
            'module' => 'reports',
            'action' => 'index',
            'resource' => 'Reports_Index',
            'privilege' => 'browse'
        );
        
        return $nav;
    }

    /**
     * Defines the ACL for the reports controllers.
     *
     * @param Array $args
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        
        $indexResource = new Zend_Acl_Resource('Reports_Index');
        $filesResource = new Zend_Acl_Resource('Reports_Files');
        $acl->add($indexResource);
        $acl->add($filesResource);
        $acl->allow(array('super', 'admin'), array('Reports_Index', 'Reports_Files'));
    }
    
    /**
     * Add Reports_Report as a searchable type.
     */
    public function filterSearchRecordTypes($recordTypes)
    {
        $recordTypes['Reports_Report'] = __('Report');
        return $recordTypes;
    }
    
    /**
     * Add the routes for the plugin
     * 
     * @param Zend_Controller_Router_Rewrite $router
     */
    public function hookDefineRoutes($args)
    {
        // Don't add these routes on the admin side to avoid conflicts.
        if (is_admin_theme()) {
            $router = $args['router'];
            $router->addRoute(
                'reports_add', 
                new Zend_Controller_Router_Route(
                    'reports/add', 
                    array(
                        'module'       => 'reports', 
                        'controller'   => 'index', 
                        'action'       => 'add' 
                    )
                )
            );
        }
    }
}
