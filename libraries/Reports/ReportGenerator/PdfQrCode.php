<?php
class Reports_ReportGenerator_PdfQrCode extends Reports_ReportGenerator
{
    private $_file;
    private $_items;
    
    public function generateReport($filename) {
        $chartUrl = 'http://chart.apis.google.com/chart';
        
        $queries = get_db()->getTable('ReportsQuery')->findByReportId($this->_report->id);
        $query = unserialize($queries[0]->query);
        $params = $this->_convertSearchFilters($query);
        $this->_items = get_db()->getTable('Item')->findBy($params);
        
        $pdf = new Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);

        $helvetica = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        $pdf->pages[0]->setFont($helvetica, 12);
        $pdf->pages[0]->drawText('Sweet!', 72, 720);

        $imagefile = file_put_contents(REPORTS_SAVE_DIRECTORY.'/temp.png', file_get_contents($chartUrl.'?cht=qr&chl=http://omeka.org/codex&chs=300x300'));

        $image = Zend_Pdf_Image::imageWithPath(REPORTS_SAVE_DIRECTORY.'temp.png');

        $pdf->pages[0]->drawImage($image, 72, 500, 144, 572);
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