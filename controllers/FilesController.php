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
        $this->_modelClass = 'ReportsFile';
    }
    
    /**
     * Deletes a ReportsFile model and deletes the underlying file.
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
