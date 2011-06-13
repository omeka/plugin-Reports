<?php
/**
 * @package Reports
 * @subpackage Controllers
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Index controller
 *
 * @package Reports
 * @subpackage Controllers
 */
class Reports_FilesController extends Omeka_Controller_Action
{
    /**
     * Sets the model class for the files controller.
     */
    public function init()
    {
        $this->_modelClass = 'Reports_File';
    }
    
    /**
     * Deletes a Reports_File instance and deletes the underlying file.
     * 
     * FIXME: Deleting the file itself should be part of that model.
     */
    public function deleteAction()
    {
        $reportFile = $this->findById();
        $report = $reportFile->getReport();
        
        $filename = REPORTS_SAVE_DIRECTORY . '/' . $reportFile->filename;
        $reportFile->delete();
        if (is_writable($filename)) {
            unlink($filename);
        }
        
        $this->redirect->gotoRoute(
            array(
                'module' => 'reports',
                'id' => $report->id,
                'action' => 'show',
            ),
            'default'
        );
    }
}
