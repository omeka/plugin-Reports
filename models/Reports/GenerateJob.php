<?php

class Reports_GenerateJob extends Omeka_JobAbstract
{
    public function perform()
    {
        $fileId = $this->_options['fileId'];
        $report = $this->_db->getTable('Reports_File')->find($fileId);
        $generator = $report->getGenerator();
        $generator->generate();
        $report->forceSave();
    }
}
