<?php

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
        $this->addElement('submit', 'submit_add_report', array(
            'label' => 'Add Report',
            'class' => 'submit-medium',
        ));
    }
}
