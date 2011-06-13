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
$generator = $reports_file->getGenerator();

header("Content-Type: {$generator->getContentType()}");
header("Content-Disposition: inline; filename=report.{$generator->getExtension()}");

//Stop output buffering to allow the output of large file
ob_end_flush();
readfile(REPORTS_SAVE_DIRECTORY . '/' . $reports_file->filename);
exit;
