<?php
class Reports_ReportGenerator_HTML extends Reports_ReportGenerator
{
    private $_file;
    private $_items;
    
    public function generateReport($filename) {
        $queries = get_db()->getTable('ReportsQuery')->findByReportId($this->_report->id);
        $query = unserialize($queries[0]->query);
        $params = $this->_convertSearchFilters($query);
        $this->_items = get_db()->getTable('Item')->findBy($params);
        
        $this->_file = fopen($filename, 'w');
        ob_start(array($this, '_fileOutputCallback'), 1);
        $this->outputHTML();
        ob_end_flush();
        fclose($this->_file);
    }
    
    private function _fileOutputCallback($buffer) {
        fwrite($this->_file, $buffer);
    }
    
    private function outputHTML() { 
        ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Omeka Report</title>
<head>
<body>
<h1>Omeka Report</h1>
<table>
<tr>
<td>Generated on <?php echo date('r') ?></td>
</tr>
</table>
<?php foreach($this->_items as $item) : ?>
<h2>Item <?php echo $item->id; ?></h2>
<?php     foreach($item->getAllElementsBySet() as $set => $elements) : ?>
<h3><?php echo $set; ?></h3>
<table>
<?php         foreach($elements as $element) : 
                  foreach($item->getTextsByElement($element) as $text) : ?>
<tr>
<td><strong><?php echo $element->name; ?></strong></td>
<td><?php echo $text->text; ?></td>
</tr>
<?php             endforeach; ?>
<?php         endforeach; ?>
</table>
<?php     endforeach; ?>
<?php endforeach; ?>
</body>
</html>
<?php
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
        return 'HTML';
    }
    public function getContentType() {
        return 'text/html';
    }
    public function getExtension() {
        return 'html';
    }
}