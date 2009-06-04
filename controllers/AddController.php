<?php
/**
 * @package Reports
 * @subpackage Controllers
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Add report controller
 *
 * @package Reports
 * @subpackage Controllers
 */
class Reports_AddController extends Omeka_Controller_Action
{
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