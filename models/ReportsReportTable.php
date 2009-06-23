<?php
/**
 * @package Reports
 * @subpackage Models
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Main report model object class.
 *
 * @package Reports
 * @subpackage Models
 */
class ReportsReportTable extends Omeka_Db_Table
{
    /**
     * Finds all report records and sorts by ID, descending.
     *
     * @return array Array of ReportsReport objects.
     */
    public function findAllReports()
    {
        $select = $this->getSelect()->order('id DESC');
        return $this->fetchObjects($select);
    }
}