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
        $reportName = $this->_report->name;
        $reportDescription = $this->_report->description;
        ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />    
<title>Omeka Report</title>
<style type="text/css" media="screen">
    body {background: #ccc; padding:0; margin:0;}
    #report {width: 920px; margin: 0 auto; padding: 20px; background:#fff;}
    .item {border-bottom: 1px solid #ccc;}
    table {border-bottom:1px dotted #ccc; width:100%;}
    th, td {vertical-align:top; padding: 10px 0;}
    th {text-align:right; font-weight:bold;}
</style>
</head>
<body>
    <div id="report">
        <h1><?php echo $reportName; ?></h1>
        <p>Generated on <?php echo date('r') ?></p>
        <?php echo $reportDescription; ?>
        <?php foreach($this->_items as $item) : ?>
            <div class="item" id="item-<?php echo $item->id; ?>">
                <h2>Item <?php echo $item->id; ?></h2>
                <?php foreach($item->getAllElementsBySet() as $set => $elements) : ?>
                <h3><?php echo $set; ?></h3>
                <table class="element-texts" cellpadding="0" cellspacing="0">
                <?php foreach($elements as $element) : ?>
                    <?php foreach($item->getTextsByElement($element) as $text) : ?>
                    <tr class="element">
                        <th scope="row" class="element-name"><?php echo $element->name; ?></th>
                        <td class="element-value"><?php echo $text->text; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                </table>
                <?php endforeach; ?>
                </div>
                <?php release_object($item); ?>
        <?php endforeach; ?>
    </div>
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