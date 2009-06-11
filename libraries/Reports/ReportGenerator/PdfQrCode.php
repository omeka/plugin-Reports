<?php

class Reports_ReportGenerator_PdfQrCode extends Reports_ReportGenerator
{
    private $_file;
    private $_items;
    private $_page;
    
    const CHART_API_URI = 'http://chart.apis.google.com/chart';
    
    /* Spacing constants for 5160 labels, in inches. */
    
    const MARGIN_LEFT = 13.5;
    const MARGIN_RIGHT = 36;
    const MARGIN_TOP = 36;
    const MARGIN_BOTTOM = 13.5;
    
    const COLUMNS = 3;
    const ROWS = 10;
    
    const HORIZONTAL_SPACING = 11.25;
    const VERTICAL_SPACING = 0;
    
    const LABEL_HEIGHT = 72;
    const LABEL_WIDTH = 189;
    
    private function _qrCodeUri($item)
    {
        $args = array('cht' => 'qr',
                      'chl' => BASE_URL.'/items/show/'.$item->id,
                      'chs' => '300x300');
        return self::CHART_API_URI.'?'.http_build_query($args);
    }
    
    private function _drawItemLabel($column, $row, $item)
    {
        $page = $this->_page;
        // Start at the bottom left corner and count over for columns and down for rows.
        $originX = self::MARGIN_LEFT + ($column * (self::LABEL_WIDTH + self::HORIZONTAL_SPACING));
        $originY = 792 - self::MARGIN_TOP - (($row + 1) * (self::LABEL_HEIGHT + self::VERTICAL_SPACING));
        
        $page->saveGS();
        
        // Clip on label boundaries to stop text from running over.
        $page->clipRectangle($originX, $originY, $originX + self::LABEL_WIDTH, $originY + self::LABEL_HEIGHT);
        
        // Temporarily save the generated QR Code.
        $temp = REPORTS_SAVE_DIRECTORY. '/qrcode.png';
        file_put_contents($temp, file_get_contents($this->_qrCodeUri($item)));
        $image = Zend_Pdf_Image::imageWithPath($temp);
        unlink($temp);
        
        $page->drawImage($image, $originX, $originY, $originX + 72, $originY + 72);
        $titles = $item->getElementTextsByElementNameAndSetName('Title', 'Dublin Core');
        if(count($titles) > 0)
        $page->drawText($titles[0]->text, $originX + 72, $originY + 50);
        
        $page->restoreGS();
    }
    
    private function addPage($pdf)
    {
        $newPage = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
        $newPage->setFont($helvetica, 8);
        $pdf->pages[] = $this->_page = $newPage;
    }
        
    
    public function generateReport($filename) {
        $chartUrl = 'http://chart.apis.google.com/chart';
        
        $queries = get_db()->getTable('ReportsQuery')->findByReportId($this->_report->id);
        $query = unserialize($queries[0]->query);
        $params = $this->_convertSearchFilters($query);
        $this->_items = get_db()->getTable('Item')->findBy($params);
        
        $pdf = new Zend_Pdf();
        $this->addPage($pdf);
        
        $this->_page = $pdf->pages[0];
        
        $column = 0;
        $row = 0;
        foreach ($this->_items as $item) {
            $this->_drawItemLabel($column, $row, $item);
            $row++;
            
            if($row >= self::ROWS) {
                $column++;
                $row = 0;
            }
            if($column >= self::COLUMNS) {
                $this->addPage($pdf);
                $column = 0;
            }
        }
        
        $pdf->save($filename);
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
                        
                // Must be logged in to view items specific to certain users
                //if (!$controller->isAllowed('browse', 'Users')) {
                    //throw new Exception( 'May not browse by specific users.' );
                //}
                
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
            
            $recent = $query['recent'];
            if ($recent !== 'false') {
                $order['recent'] = true;
            }
            
            if ($search = $query['search']) {
                $filter['search'] = $search;
                //Don't order by recent-ness if we're doing a search
                unset($order['recent']);
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
    
    public function getReadableName() {
        return 'QR Code (PDF)';
    }
    public function getContentType() {
        return 'applicaton/pdf';
    }
    public function getExtension() {
        return 'pdf';
    }
}