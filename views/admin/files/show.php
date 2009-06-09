<?php
/**
 * File viewer route.
 *
 * Passes through files from the generated reports directory
 *
 * @package Reports
 * @subpackage Views
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

header("Content-Type: $reportsfile->getContentType()");
header("Content-Disposition: inline; filename=report.html");

readfile($reportsfile->path);