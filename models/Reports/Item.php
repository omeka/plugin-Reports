<?php
/**
 * @package Reports
 * @subpackage Models
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Model class for included/excluded items.
 *
 * @package Reports
 * @subpackage Models
 */
class ReportsItem extends Omeka_Record
{
    public $id;
    public $report_id;
    public $item_id;
}