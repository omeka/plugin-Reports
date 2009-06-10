<?php
abstract class Reports_ReportGenerator
{
    /**
     * Search parameters
     * @var ReportsFile
     */
    protected $_reportFile;
    
    protected $_report;
    
    protected $_item;
    
    //protected $filename;
    
    public function __construct($reportFile) {
        if($reportFile)
        {
            $reportFile->status = ReportsFile::STATUS_IN_PROGRESS;
            $reportFile->save();
            $this->_reportFile = $reportFile;
        
            $this->_report = $this->_reportFile->getReport();
            
            $filename = tempnam(REPORTS_SAVE_DIRECTORY, 'report');
            
            $this->generateReport($filename);
        
            $this->_reportFile->status = ReportsFile::STATUS_COMPLETED;
            $this->_reportFile->path = $filename;
            $this->_reportFile->save();
        }
    }
    
    public abstract function generateReport($filename);
    
    public abstract function getReadableName();
    
    public abstract function getContentType();
    
    public abstract function getExtension();
}