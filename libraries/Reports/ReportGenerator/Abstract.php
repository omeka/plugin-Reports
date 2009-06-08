<?php
class Reports_ReportGenerator_Abstract
{
    /**
     * Search parameters
     * @var array
     */
    protected $reportFile;
    
    public __construct($reportFile) {
        $reportFile->status = 'in progress';
        $reportFile->save();
        $this->reportFile = $reportFile;
    }
    
    public abstract function generateReport();
}