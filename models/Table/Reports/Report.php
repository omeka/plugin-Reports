<?php
/**
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */
 
/**
 * Main report model object class.
 *
 * @package Reports
 * @subpackage Models
 */
class Table_Reports_Report extends Omeka_Db_Table
{
    protected $_name = 'reports';

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