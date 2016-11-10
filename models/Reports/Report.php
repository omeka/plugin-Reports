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
class Reports_Report extends Omeka_Record_AbstractRecord
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

    protected function _initializeMixins()
    {
        // Add the search mixin.
        $this->_mixins[] = new Mixin_Search($this);
        $this->_mixins[] = new Mixin_Timestamp($this, 'modified', null);
    }

    protected function beforeSave($args)
    {
        $this->creator = current_user()->id;
        
        // Make the report searchable by admins
        $this->setSearchTextPrivate();
        $this->setSearchTextTitle($this->name);
        $this->addSearchText($this->name);
        $this->addSearchText($this->description);
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
        return $user->name;
    }

    /**
     * Get the routing parameters or the URL string to this record.
     * 
     * @param string $action
     * @return string|array A URL string or a routing array.
     */
    public function getRecordUrl($action = 'show')
    {
        return array(
            'module' => 'reports',
            'controller' => 'index',
            'action' => $action,
            'id' => $this->id
        );
    }
}
