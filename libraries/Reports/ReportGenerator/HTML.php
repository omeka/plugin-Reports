<?php
class Reports_ReportGenerator_HTML extends Reports_ReportGenerator
{
    private $_file;
    private $_items;
    
    public function generateReport($filename) {
        $this->_items = get_db()->getTable('Item')->findBy($this->_params);
        
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