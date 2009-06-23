<?php
abstract class Reports_ReportGenerator
{
    /**
     * Model object for file to be generated.
     * @var ReportsFile
     */
    protected $_reportFile;
    
    protected $_report;
    
    protected $_params;
    
    public function __construct($reportFile) {
        if ($reportFile)
        {
            set_error_handler($this, 'errorHandler', E_WARNING);
            try {
                $reportFile->status = ReportsFile::STATUS_IN_PROGRESS;
                $reportFile->save();
                $this->_reportFile = $reportFile;
        
                $this->_report = $this->_reportFile->getReport();
                $this->_params = reports_convertSearchFilters(unserialize($this->_report->query));
            
                $filter = new Omeka_Filter_Filename();
                $filename = $filter->renameFileForArchive('report'.$this->getExtension());
                $path = REPORTS_SAVE_DIRECTORY . DIRECTORY_SEPARATOR . $filename;
            
                $this->generateReport($path);
        
                $this->_reportFile->status = ReportsFile::STATUS_COMPLETED;
                $this->_reportFile->filename = $filename;
            } catch (Exception $e) {
                $this->_reportFile->status = ReportsFile::STATUS_ERROR;
                $this->_addStatusMessage($e->getMessage(), 'Error');
            }
            $this->_reportFile->pid = null;
            $memoryKiB = (int) (memory_get_peak_usage() / 1024);
            $this->_addStatusMessage("Peak memory usage: $memoryKiB KiB");
            $this->_reportFile->save();
        }
    }
    
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $this->_addStatusMessage("$errstr in $errfile on $errline", 'PHP Warning');
        $this->reportFile->save();
        return true;
    }
    
    final protected function _addStatusMessage($message, $prepend = 'Notice', $delimiter = "\n")
    {
        if (strlen($this->_reportFile->messages) == 0) {
            $delimiter = '';
        }
        $date = date('Y-m-d H:i:s O');
        $this->_reportFile->messages .= "$delimiter$date: $prepend: $message";
    }
    
    public abstract function generateReport($path);
    
    public abstract function getReadableName();
    
    public abstract function getContentType();
    
    public abstract function getExtension();
}