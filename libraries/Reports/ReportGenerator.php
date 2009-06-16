<?php
abstract class Reports_ReportGenerator
{
    /**
     * Search parameters
     * @var ReportsFile
     */
    protected $_reportFile;
    
    protected $_report;
    
    public function __construct($reportFile) {
        if($reportFile)
        {
            $reportFile->status = ReportsFile::STATUS_IN_PROGRESS;
            $reportFile->save();
            $this->_reportFile = $reportFile;
        
            $this->_report = $this->_reportFile->getReport();
            
            $filter = new Omeka_Filter_Filename();
            $filename = $filter->renameFileForArchive('report'.$this->getExtension());
            $path = REPORTS_SAVE_DIRECTORY . DIRECTORY_SEPARATOR . $filename;
            
            $this->generateReport($path);
        
            $this->_reportFile->status = ReportsFile::STATUS_COMPLETED;
            $this->_reportFile->filename = $filename;
            $this->_reportFile->save();
        }
    }
    
    public abstract function generateReport($filename);
    
    public abstract function getReadableName();
    
    public abstract function getContentType();
    
    public abstract function getExtension();
}