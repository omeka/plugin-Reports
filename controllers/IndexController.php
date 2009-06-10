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
    
    public function browseAction()
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
            $id = $report->id;
            $creator = $report->creator;
            
            $userName = $this->getTable('Entity')->find($creator)->getName();
            $queries = $this->getTable('ReportsQuery')->findByReportId($id);
            
            $count = 0;
            foreach($queries as $query) {
                $query = unserialize($query->query);
                $params = $this->_convertSearchFilters($query);
                
                $itemTable = $this->getTable('Item');
                
                $count += $itemTable->count($params);
            }
            
            $reportsDisplay[] = array(
                'reportObject' => $report,
                'userName' => $userName,
                'count' => $count);
        }
        $this->view->reports = $reportsDisplay;
    }
    
    public function queryAction()
    {
        $report = $this->findById();
        
        if(isset($_GET['advanced'])) {
            $query = new ReportsQuery;
            $query->report_id = $report->id;
            $query->query = serialize($_GET);
            $query->save();
            $this->redirect->goto('index');
        }
    }
    
    private function _convertSearchFilters($query) {
        $perms  = array();
        $filter = array();
        $order  = array();
        
        //Show only public items
        if ($query['public']) {
            $perms['public'] = true;
        }
        
        //Here we add some filtering for the request    
        try {
            
            // User-specific item browsing
            if ($userToView = $query['user']) {
                        
                // Must be logged in to view items specific to certain users
                if (!$controller->isAllowed('browse', 'Users')) {
                    throw new Exception( 'May not browse by specific users.' );
                }
                
                if (is_numeric($userToView)) {
                    $filter['user'] = $userToView;
                }
            }

            if ($query['featured']) {
                $filter['featured'] = true;
            }
            
            if ($collection = $query['collection']) {
                $filter['collection'] = $collection;
            }
            
            if ($type = $query['type']) {
                $filter['type'] = $type;
            }
            
            if (($tag = $query['tag']) || ($tag = $query['tags'])) {
                $filter['tags'] = $tag;
            }
            
            if ($excludeTags = $query['excludeTags']) {
                $filter['excludeTags'] = $excludeTags;
            }
            
            $recent = $query['recent'];
            if ($recent !== 'false') {
                $order['recent'] = true;
            }
            
            if ($search = $query['search']) {
                $filter['search'] = $search;
                //Don't order by recent-ness if we're doing a search
                unset($order['recent']);
            }
            
            //The advanced or 'itunes' search
            if ($advanced = $query['advanced']) {
                
                //We need to filter out the empty entries if any were provided
                foreach ($advanced as $k => $entry) {                    
                    if (empty($entry['element_id']) || empty($entry['type'])) {
                        unset($advanced[$k]);
                    }
                }
                $filter['advanced_search'] = $advanced;
            };
            
            if ($range = $query['range']) {
                $filter['range'] = $range;
            }
            
        } catch (Exception $e) {
            $controller->flash($e->getMessage());
        }
        return array_merge($perms, $filter, $order);
    }
    
    public function showAction()
    {
        $report = $this->findById();
        
        $reportFiles = $this->getTable('ReportsFile')->findByReportId($report->id);
        
        $this->view->reportsreport = $report;
        $this->view->reportFiles = $reportFiles;
    }
    
    public function generateAction()
    {
        $report = $this->findById();
        
        $reportFile = new ReportsFile();
        $reportFile->report_id = $report->id;
        $reportFile->type = "html";
        $reportFile->status = ReportsFile::STATUS_STARTING;
        $reportFile->save();
        $this->redirect->gotoRoute(array('id' => "$report->id",
                                         'action' => 'show'),
                                   'reports-id-action');
    }
}