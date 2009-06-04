<?php
/**
 * @package Reports
 * @subpackage Models
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Model class for generated report files.
 *
 * @package Reports
 * @subpackage Models
 */
class ReportsFile extends Omeka_Record
{
    public $id;
    public $report_id;
    public $type;
    public $path;
    public $status;
    public $created;
}