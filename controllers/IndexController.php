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
    /**
     * Sets the model class for the Reports controller.
     */
    public function init()
    {
        $this->_helper->db->setDefaultModelName('Reports_Report');
    }
    
    /**
     * Displays the browse page for all reports.
     */
    public function browseAction()
    {
        if(!is_writable(REPORTS_SAVE_DIRECTORY)) {
            $this->flash('Warning: The directory '.REPORTS_SAVE_DIRECTORY.
                         ' must be writable by the server for reports to be'.
                         ' generated.', Omeka_Controller_Flash::ALERT);
        }
        
        // FIXME: Switch to Zend_Http_Client to remove this dependency.
        if(ini_get('allow_url_fopen') != 1) {
            $this->flash('Warning: The PHP directive "allow_url_fopen" is set'.
                         ' to false.  You will be unable to generate QR Code'.
                         ' reports.', Omeka_Controller_Flash::ALERT);
        }
        
        $reports = $this->getTable('Reports_Report')->findAllReports();
        foreach($reports as $report) {
            $id = $report->id;
            $creator = $report->creator;
            
            $userName = $this->getTable('Entity')->find($creator)->getName();
            $query = unserialize($report->query);
            $params = reports_convertSearchFilters($query);
            $count = $this->getTable('Item')->count($params);
            
            $reportsDisplay[] = array(
                'reportObject' => $report,
                'userName' => $userName,
                'count' => $count);
        }
        $this->view->reports = $reportsDisplay;
        $this->view->formats = reports_getOutputFormats();
    }
    
    public function addAction()
    {
        $varName = strtolower($this->_modelClass);
        $class = $this->_modelClass;
        
        $record = new $class();
        
        try {
            if ($record->saveForm($_POST)) {
                $this->redirect->gotoRoute(array('id'     => "$record->id",
                                                 'action' => 'query'),
                                           'reports-id-action');
            }
        } catch (Omeka_Validator_Exception $e) {
            $this->flashValidationErrors($e);
        }
        $this->view->assign(array($varName=>$record));
    }
    
    /**
     * Edits the filter for a report.
     */
    public function queryAction()
    {
        $report = $this->findById();
        
        if(isset($_GET['search'])) {
            $report->query = serialize($_GET);
            $report->save();
            $this->redirect->goto('index');
        } 
        else {
            $queryArray = unserialize($report->query);
            // Some parts of the advanced search check $_GET, others check
            // $_REQUEST, so we set both to be able to edit a previous query.
            $_GET = $queryArray;
            $_REQUEST = $queryArray;
            $this->view->reportsreport = $report;
        }
        
    }
    
    /**
     * Shows details and generated files for a report.
     */
    public function showAction()
    {
        $report = $this->findById();
        
        $reportFiles = $report->getFiles();
        
        $formats = reports_getOutputFormats();
        
        $this->view->formats = $formats;
        
        $this->view->reportsreport = $report;
        $this->view->reportFiles = $reportFiles;
    }
    
    /**
     * Spawns a background process to generate a new report file.
     */
    public function generateAction()
    {
        $report = $this->findById();
        
        if (!is_writable(REPORTS_SAVE_DIRECTORY)) {
            // Disallow generation if the save directory is not writable
            $this->flash('The directory '.REPORTS_SAVE_DIRECTORY.
                         ' must be writable by the server for reports to be'.
                         ' generated.',
                         Omeka_Controller_Flash::GENERAL_ERROR);
            return;             
        } 
        $reportFile = new Reports_File();
        $reportFile->report_id = $report->id;
        $reportFile->type = $_GET['format'];
        $reportFile->status = Reports_File::STATUS_STARTING;
    
        // Send the base URL to the background process for QR Code
        // This should be abstracted out to work more generally for
        // all generators.
        if($reportFile->type == 'PdfQrCode') {
            $reportFile->options = serialize(array('baseUrl' => WEB_ROOT));
        }
    
        $reportFile->save();
        
        throw new Exception("Use Omeka_Job.");
        $report = $db->getTable('Reports_File')->find($reportFile->id);

        // Get the report type (corresponds to the name of the class)
        $reportType = $report->type;

        // Set the report generator class.
        $generatorClass = 'Reports_Generator_'.$reportType;

        new $generatorClass($report);
        $reportFile->save();
        $this->redirect->gotoRoute(array('id'     => "$report->id",
                                         'action' => 'show'),
                                   'reports-id-action');
    }
}
