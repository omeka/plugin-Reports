<?php
abstract class Reports_ReportGenerator
{
    /**
     * Search parameters
     * @var ReportsFile
     */
    protected $_reportFile;
    
    protected $_report;
    
    protected $_params;
    
    public function __construct($reportFile) {
        if($reportFile)
        {
            $reportFile->status = ReportsFile::STATUS_IN_PROGRESS;
            $reportFile->save();
            $this->_reportFile = $reportFile;
        
            $this->_report = $this->_reportFile->getReport();
            $this->_params = $this->_convertSearchFilters(unserialize($this->_report->query));
            
            $filter = new Omeka_Filter_Filename();
            $filename = $filter->renameFileForArchive('report'.$this->getExtension());
            $path = REPORTS_SAVE_DIRECTORY . DIRECTORY_SEPARATOR . $filename;
            
            $this->generateReport($path);
        
            $this->_reportFile->status = ReportsFile::STATUS_COMPLETED;
            $this->_reportFile->filename = $filename;
            $this->_reportFile->save();
        }
    }
    
    private function _convertSearchFilters($query) {
        $perms  = array();
        $filter = array();
        $order  = array();
        
        //Show only public items
        if ($query['public']) {
            $perms['public'] = true;
        }
        
        //Here we add some filtering for the request    
        try {
            
            // User-specific item browsing
            if ($userToView = $query['user']) {
                if (is_numeric($userToView)) {
                    $filter['user'] = $userToView;
                }
            }

            if ($query['featured']) {
                $filter['featured'] = true;
            }
            
            if ($collection = $query['collection']) {
                $filter['collection'] = $collection;
            }
            
            if ($type = $query['type']) {
                $filter['type'] = $type;
            }
            
            if (($tag = $query['tag']) || ($tag = $query['tags'])) {
                $filter['tags'] = $tag;
            }
            
            if ($excludeTags = $query['excludeTags']) {
                $filter['excludeTags'] = $excludeTags;
            }
            
            //The advanced or 'itunes' search
            if ($advanced = $query['advanced']) {
                
                //We need to filter out the empty entries if any were provided
                foreach ($advanced as $k => $entry) {                    
                    if (empty($entry['element_id']) || empty($entry['type'])) {
                        unset($advanced[$k]);
                    }
                }
                $filter['advanced_search'] = $advanced;
            };
            
            if ($range = $query['range']) {
                $filter['range'] = $range;
            }
            
        } catch (Exception $e) {
        }
        return array_merge($perms, $filter, $order);
    }
    
    public abstract function generateReport($path);
    
    public abstract function getReadableName();
    
    public abstract function getContentType();
    
    public abstract function getExtension();
}