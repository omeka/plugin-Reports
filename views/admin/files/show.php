<?php
/**
 * File viewer route.
 *
 * Passes through files from the generated reports directory
 *
 * @package Reports
 * @subpackage Views
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * FIXME: Use chunked version of readfile() to avoid dying from going over
 * the memory limit.
 */
$generator = $reports_file->getGenerator();

header("Content-Type: {$generator->getContentType()}");
header("Content-Disposition: inline; filename=report.{$generator->getExtension()}");

//Stop output buffering to allow the output of large file
ob_end_flush();
readfile(reports_save_directory() . '/' . $reports_file->filename);
exit;
