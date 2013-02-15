<?php
/**
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */
 
class Reports_GenerateJob extends Omeka_Job_AbstractJob
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
        $report->save();
    }
}
