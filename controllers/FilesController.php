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
    public function init()
    {
        $this->_modelClass = 'ReportsFile';
    }
    
    public function deleteAction()
    {
        $reportFile = $this->findById();
        $report = $reportFile->getReport();
        
        $filename = $reportFile->filename;
        $reportFile->delete();
        unlink(REPORTS_SAVE_DIRECTORY.DIRECTORY_SEPARATOR.$filename);
        
        $this->redirect->gotoRoute(array('id' => "$report->id",
                                         'action' => 'show'),
                                   'reports-id-action');
    }
}