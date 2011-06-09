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
class ReportsReport extends Omeka_Record
{
    public $id;
    public $name;
    public $description;
    public $query;
    public $creator;
    public $modified;
    
    /**
     * Throw validation errors for report form.
     */
    protected function _validate()
    {
        if (empty($this->name)) {
            $this->addError('name', 'Report must be given a valid name.');
        }
        
        if (strlen($this->name) > 255) {
            $this->addError('name', 'Report name must be less than 255 characters.');
        }
    }
    
    /**
     * Gets all the generated files for this report.
     *
     * @return array Array of ReportsFile objects.
     */
    public function getFiles()
    {
        return $this->_db->getTable('ReportsFile')->findByReportId($this->id);
    }
}