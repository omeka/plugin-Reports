<?php

class Reports_GenerateJob extends Omeka_JobAbstract
{
    public function perform()
    {
        if ($memoryLimit = reports_get_config('memoryLimit')) {
            ini_set('memory_limit', $memoryLimit);
            _log("Set memory limit to $memoryLimit");
        }
        $fileId = $this->_options['fileId'];
        $report = $this->_db->getTable('Reports_File')->find($fileId);
        $generator = $report->getGenerator();
        $generator->generate();
        $report->forceSave();
    }
}
