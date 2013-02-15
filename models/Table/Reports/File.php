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
class Table_Reports_File extends Omeka_Db_Table
{
    /**
     * Finds all report files and sorts by creation date, descending.
     *
     * @return array Array of ReportsReport objects.
     */
    public function findByReportId($reportId) {
        $select = $this->getSelect()->where('report_id = ?', $reportId)->order('created DESC');
        return $this->fetchObjects($select);
    }
}