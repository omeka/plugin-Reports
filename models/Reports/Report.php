<?php
/**
 * @package Reports
 * @subpackage Models
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Main report model object class.
 *
 * @package Reports
 * @subpackage Models
 */
class Reports_Report extends Omeka_Record
{
    public $id;
    public $name;
    public $description;
    public $query;

    /**
     * @var integer User ID.
     */
    public $creator;
    public $modified;

    protected function beforeInsert()
    {
        $this->creator = current_user()->id;
    }
    
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
     * @return array Array of Reports_File objects.
     */
    public function getFiles()
    {
        return $this->_db->getTable('Reports_File')->findByReportId($this->id);
    }

    public function getCreatorName()
    {
        $user = $this->getTable('User')->find($this->creator);
        return $user->first_name . ' ' . $user->last_name;
    }
}
