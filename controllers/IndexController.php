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
class Reports_IndexController extends Omeka_Controller_Action
{
    public function init()
    {
        $this->_modelClass = 'ReportsReport';
    }
    
    public function indexAction()
    {
        if(!is_writable(REPORTS_SAVE_DIRECTORY)) {
            $this->flash('Warning: The directory '.REPORTS_SAVE_DIRECTORY.
                         ' must be writable by the server for reports to be'.
                         ' generated.', Omeka_Controller_Flash::ALERT);
        }
        
        if(ini_get('allow_url_fopen') != 1) {
            $this->flash('Warning: The PHP directive "allow_url_fopen" is set'.
                         ' to false.  You will be unable to generate QR Code'.
                         ' reports.', Omeka_Controller_Flash::ALERT);
        }
        
        $reports = $this->getTable('ReportsReport')->findAllReports();
        foreach($reports as $report) {
            $userName = $this->getTable('User')->find($report->creator)->username;
            $reportsDisplay[] = array(
                'reportObject' => $report,
                'userName' => $userName);
        }
        $this->view->reports = $reportsDisplay;
    }
    
    public function addAction()
    {
        
    }
    
    public function submitAction()
    {
        $report = new ReportsReport();
        
        $report->name = $_POST['name'];
        $report->description = $_POST['description'];
        $report->creator = Omeka_Context::getInstance()->getCurrentUser()->entity_id;
        
        $report->save();
        $this->redirect->goto('index');
    }
}