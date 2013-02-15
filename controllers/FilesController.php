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
class Reports_FilesController extends Omeka_Controller_AbstractActionController
{
    /**
     * Sets the model class for the files controller.
     */
    public function init()
    {
        $this->_helper->db->setDefaultModelName('Reports_File');
    }

    public function showAction()
    {
        $reportsFile = $this->_helper->db->findById();
        $g = $reportsFile->getGenerator();        
        $storage = $g->getStorage();        
        $storagePrefixDir = $g->getStoragePrefixDir();
        $destPath = $storagePrefixDir . '/' . $reportsFile->filename;
        $uri = $storage->getUri($destPath);
                
        return $this->_helper->redirector->gotoUrl($uri);
    }
    
    /**
     * Deletes a Reports_File instance and deletes the underlying file.
     */
    public function deleteAction()
    {
        $reportFile = $this->_helper->db->findById();
        $report = $reportFile->getReport();
        $reportFile->delete();
        $this->_helper->redirector->gotoRoute(
            array(
                'module' => 'reports',
                'controller' => 'index',
                'id' => $report->id,
                'action' => 'show',
            ),
            'default'
        );
    }
}