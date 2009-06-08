<?php
/**
 * File viewer route.
 *
 * Provides the main landing page of the administrative interface.
 *
 * @package Reports
 * @subpackage Views
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=report.html');

readfile($filename);