<?php

class Reports_GenerateJob extends Omeka_JobAbstract
{
    public function perform()
    {
        $fileId = $this->_options['fileId'];
        $report = $this->_db->getTable('Reports_File')->find($fileId);

        // Type corresponds to the name of the class.
        $reportType = $report->type;
        $generatorClass = 'Reports_Generator_'.$reportType;

        $generator = new $generatorClass($report);
        $generator->generate();
        $report->forceSave();
    }
}
