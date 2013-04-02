<?php
/**
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */
 
/**
 * Model class for included/excluded items.
 *
 * @package Reports
 * @subpackage Models
 */
class Reports_Item extends Omeka_Record_AbstractRecord
{
    public $id;
    public $report_id;
    public $item_id;
}
