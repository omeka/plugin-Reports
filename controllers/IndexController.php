<?php
/**
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */
 
/**
 * Index controller
 *
 * @package Reports
 * @subpackage Controllers
 */
class Reports_IndexController extends Omeka_Controller_AbstractActionController
{
    private $_jobDispatcher;

    protected $_browseRecordsPerPage = 10;

    /**
     * Sets the model class for the Reports controller.
     */
    public function init()
    {
        $this->_helper->db->setDefaultModelName('Reports_Report');
        $this->_jobDispatcher = Zend_Registry::get('bootstrap')->getResource('jobs');
    }
    
    /**
     * Displays the browse page for all reports.
     */
    public function browseAction()
    {   
        $db = $this->_helper->db;
        
        if (!$this->_getParam('sort_field')) {
            $this->_setParam('sort_field', 'added');
            $this->_setParam('sort_dir', 'd');
        }
        
        parent::browseAction();        
        
        $reportItemCounts = array();
        $reportUserNames = array();
        foreach($this->view->reports_reports as $report) {            
            $user = $db->getTable('User')->find($report->creator);
            $query = unserialize($report->query);
            $itemCount = $db->getTable('Item')->count($query);
            $reportItemCounts[(string)$report->id] = $itemCount;
        }
 
        $this->view->reportItemCounts = $reportItemCounts;
        $this->view->formats = reports_get_output_formats();
    }
    
    public function addAction()
    {
        $record = new Reports_Report();
        require_once REPORTS_PLUGIN_DIRECTORY . '/forms/Reports/Detail.php';
        $form = new Reports_Form_Detail();
        $this->view->form = $form;
        $this->view->assign(array('report' => $record));
        
        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {            
            $record->setPostData($form->getValues());
            if ($record->save()) {
                $this->_helper->redirector->gotoRoute(
                    array(
                        'module' => 'reports',
                        'id'     => $record->id,
                        'action' => 'query'
                    ),
                    'default'
                );
            }
        }
    }
    
    /**
     * Edits the filter for a report.
     */
    public function queryAction()
    {
        $report = $this->_helper->db->findById();
        if (isset($_GET['search'])) {
            $report->query = serialize($_GET);            
            $report->save();
            $this->_helper->redirector->goto('index');                        
        } else {
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
        $report = $this->_helper->db->findById();
        $reportFiles = $report->getFiles();
        $formats = reports_get_output_formats();
        $this->view->formats = $formats;
        $this->view->report = $report;
        $this->view->reportFiles = $reportFiles;
    }
    
    /**
     * Spawns a background process to generate a new report file.
     */
    public function generateAction()
    {
        $report = $this->_helper->db->findById();
        
        $reportFile = new Reports_File();
        $reportFile->report_id = $report->id;
        $reportFile->type = $_GET['format'];
        $reportFile->status = Reports_File::STATUS_STARTING;
    
        // Send the base URL to the background process for QR Code
        // This should be abstracted out to work more generally for
        // all generators.
        if ($reportFile->type == 'PdfQrCode') {
            $reportFile->options = serialize(array('baseUrl' => WEB_ROOT));
        }
        
        $errors = array();
        if (!$reportFile->canStore($errors)) {
            $errorMessage = __('The report cannot be saved.  Please check your report storage settings.');
            foreach($errors as $error) {
                $errorMessage .= ' ' . $error;
            }
            $this->_helper->flashMessenger($errorMessage, 'error');
        } else {
            $reportFile->save();

            $this->_jobDispatcher->setQueueName('reports');
            $this->_jobDispatcher->sendLongRunning('Reports_GenerateJob',
                array(
                    'fileId' => $reportFile->id,
                )
            );
        }
        
        $this->_helper->redirector->gotoRoute(
            array(
                'module' => 'reports',
                'id'     => $report->id,
                'action' => 'show',
            ),
            'default'
        );
    }

    public function editAction()
    {
        throw new Omeka_Controller_Exception_404;
    }
}
