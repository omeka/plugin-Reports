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
    const STATUS_STARTING    = 'starting';
    const STATUS_IN_PROGRESS = 'in progess';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_ERROR       = 'error';
    
    public $id;
    public $report_id;
    public $type;
    public $path;
    public $status;
    public $created;
    
    public function getReport()
    {
        return get_db()->getTable('ReportsReport')->find($this->report_id);
    }
    
    public function getGenerator()
    {
        $formats = reports_getOutputFormats();
        return 'Reports_ReportGenerator_'.$this->type;
    }
}