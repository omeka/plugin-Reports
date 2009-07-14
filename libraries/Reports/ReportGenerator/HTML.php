<?php
/**
 * @package Reports
 * @subpackage Generators
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Report generator for tabular HTML output.
 *
 * @package Reports
 * @subpackage Generators
 */
class Reports_ReportGenerator_HTML extends Reports_ReportGenerator
{
    /**
     * The file handle to output to
     *
     * @var resource
     */
    private $_file;
    
    /**
     * Creates and generates the HTML report for the items in the report.
     *
     * @param string $filename The filename of the file to be generated
     */
    public function generateReport($filename) {
        $this->_file = fopen($filename, 'w');
        ob_start(array($this, '_fileOutputCallback'), 1);
        $this->outputHTML();
        ob_end_flush();
        fclose($this->_file);
    }
    
    /**
     * Callback to redirect PHP output to a file, using output buffering.
     *
     * @param string $buffer The data to be output
     */
    private function _fileOutputCallback($buffer) {
        fwrite($this->_file, $buffer);
    }
    
    /**
     * Generates the HTML document for the report.
     */
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
    th, td {vertical-align:top; padding: 10px;}
    th {text-align:right; font-weight:bold; width:100px}
</style>
</head>
<body>
    <div id="report"> 
        <h1><?php echo $reportName; ?></h1>
        <p>Generated on <?php echo date('Y-m-d H:i:s O') ?></p>
        <p><?php echo $reportDescription; ?></p>
<?php $page = 1;
    while ($items = get_db()->getTable('Item')->findBy($this->_params, 30, $page)):
        foreach ($items as $item) : ?>
            <div class="item" id="item-<?php echo $item->id; ?>">
                <h2>Item <?php echo $item->id; ?></h2>
<?php       foreach ($item->getAllElementsBySet() as $set => $elements) : 
                if (count($elements)): ?>
                <h3><?php echo $set; ?></h3>
                <table class="element-texts" cellpadding="0" cellspacing="0">
<?php               foreach ($elements as $element) :
                        foreach ($item->getTextsByElement($element) as $text) : ?>
                    <tr class="element">
                        <th scope="row" class="element-name"><?php echo $element->name; ?></th>
                        <td class="element-value"><?php echo $text->text; ?></td>
                    </tr>
<?php                   endforeach;
                    endforeach; ?>
                </table>
<?php           endif;
            endforeach;
            $tags = $item->getTags(); 
            if (count($tags)): ?>
                <h3>Tags</h3>
                <table class="element-texts" cellpadding="0" cellspacing="0">
                    <tr class="element">
                        <td class="element-value"><?php echo implode($tags, ', '); ?></td>
                    </tr>
                </table>
<?php       endif; ?>
            </div>
<?php       release_object($item); 
        endforeach;
        $page++;
    endwhile; ?>
    </div>
</body>
</html>
<?php
    }
    
    /**
     * Returns the readable name of this output format.
     *
     * @return string Human-readable name for output format
     */
    public function getReadableName() {
        return 'HTML';
    }
    
    /**
     * Returns the HTTP content type to declare for the output format.
     *
     * @return string HTTP Content-type
     */
    public function getContentType() {
        return 'text/html';
    }
    
    /**
     * Returns the file extension to append to the generated report.
     *
     * @return string File extension
     */
    public function getExtension() {
        return 'html';
    }
}