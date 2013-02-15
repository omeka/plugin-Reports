<?php
/**
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */
 
class Reports_Form_Detail extends Omeka_Form
{
    public function init()
    {
        parent::init();
        $this->addElement('text', 'name', array(
            'label' => 'Report Name',
            'size' => 60,
            'required' => true,
        ));
        $this->addElement('textarea', 'description', array(
            'label' => 'Description',
            'rows' => '10',
            'cols' => '60',
        ));
        // $this->addElement('submit', 'submit_add_report', array(
        //     'label' => 'Add Report',
        //     'class' => 'submit-medium',
        // ));
    }
}