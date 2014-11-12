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

    public function browseAction()
    {
        throw new Omeka_Controller_Exception_404;
    }

    public function showAction()
    {
        throw new Omeka_Controller_Exception_404;
    }

    public function editAction()
    {
        throw new Omeka_Controller_Exception_404;
    }

    protected function _redirectAfterDelete($record)
    {
        $report = $record->getReport();
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
