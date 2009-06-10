<?php
/**
 * Bootstrap file for the background harvesting process.
 * 
 * @package Reports
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Require the necessary files. There is probably a better way to do this.
$baseDir = str_replace('plugins/Reports', '', dirname(__FILE__));
require "{$baseDir}paths.php";
require "{$baseDir}application/libraries/Omeka/Core.php";

// Load only the required core phases.
$core = new Omeka_Core;
$core->phasedLoading('initializePluginBroker');

// Set the command line arguments.
$options = getopt('r:');

// Get the database object.
$db = get_db();

// Get the report to be generated
$reportId = $options['r'];
$report = $db->getTable('ReportsFile')->find($reportId);

// Get the report type (corresponds to the name of the class)
$reportType = $report->type;

// Set the metadata prefix class.
$metadataClass = 'Reports_ReportGenerator_'.$reportType;

require_once 'Reports/ReportGenerator/Abstract.php';
require_once 'Reports/ReportGenerator/HTML.php';
//require_once OAIPMH_HARVESTER_MAPS_DIRECTORY . "/$reportType.php";

// Set the harvest object.
//new $metadataClass($report);
new Reports_ReportGenerator_HTML($report);