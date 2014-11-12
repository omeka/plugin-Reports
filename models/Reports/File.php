<?php
/**
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */
 
/**
 * Model class for generated report files.
 *
 * @package Reports
 * @subpackage Models
 */
class Reports_File extends Omeka_Record_AbstractRecord
{
    const STATUS_STARTING    = 'starting';
    const STATUS_IN_PROGRESS = 'in progress';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_ERROR       = 'error';
    
    public $id;
    public $report_id;
    public $type;
    public $filename;
    public $status;
    public $messages;
    public $created;
    public $options;

    protected $_generator;

    /**
     * Unlink the associated file.
     */    
    protected function afterDelete()
    {
        $g = $this->getGenerator();
        $g->deleteFile($this->filename);
    }

    /**
     * Gets the report associated with this object.
     *
     * @return ReportsReport The report associated with the file.
     */
    public function getReport()
    {
        if ($report_id = $this->report_id) {
            return $this->_db->getTable('Reports_Report')->find($report_id);
        }
    }
    
    /**
     * Gets the report generator used for this file.
     *
     * @return string Name of the report generator class.
     */
    public function getGenerator()
    {
        if (!$this->_generator) {
            $this->_generator = Reports_Generator::factory($this);
        }
        return $this->_generator;
    }

     protected function _initializeMixins()
     {
         $this->_mixins[] = new Mixin_Timestamp($this, 'created', null);
     }

    /**
     * Returns whether the report can be stored in the associated Omeka_Storage object
     *
     * @param array &$errors The array of errors for why a file cannot store
     * @return bool whether the report can be stored in the associated Omeka_Storage object  
     */
    public function canStore(&$errors)
    {
        return $this->getGenerator()->canStore($errors);
    }

    /**
     * Get the URL to the saved report file.
     */
    public function getUrl()
    {
        $g = $this->getGenerator();
        $storage = $g->getStorage();
        $storagePrefixDir = $g->getStoragePrefixDir();
        $destPath = $storagePrefixDir . '/' . $this->filename;
        return $storage->getUri($destPath);
    }
}
