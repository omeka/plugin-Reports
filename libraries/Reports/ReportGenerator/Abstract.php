<?php
class Reports_ReportGenerator_Abstract
{
    /**
     * Search parameters
     * @var array
     */
    protected $params;
    
    public __construct($params) {
        $this->params = $params;
    }
}