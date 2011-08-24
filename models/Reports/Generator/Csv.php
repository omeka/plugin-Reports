<?php
/**
 * @package Reports
 * @subpackage Generators
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Report generator for Csv output.
 *
 * @package Reports
 * @subpackage Generators
 */
 
class Reports_Generator_Csv
    extends Reports_Generator
    implements Reports_GeneratorInterface
{
    /**
     * The file handle to output to
     *
     * @var resource
     */
    private $_file;
        
    /**
     * Creates and generates the Csv report for the items in the report.
     *
     * @param string $filePath
     */
    public function generateReport($filePath) {
        $this->_file = fopen($filePath, 'w');
        ob_start(array($this, '_fileOutputCallback'), 1);
        $this->outputCsv();
        ob_end_flush();
        fclose($this->_file);
        return $filePath;
    }
    
    /**
     * Output the Csv data
     */
    private function outputCsv() {
        $this->outputHeaders();
        $page = 1;
        while($items = get_db()->getTable('Item')->findBy($this->_params, 30, $page)) {
            foreach($items as $item) {
                $this->outputItemCsv($item);
            }
        $page++;
        }
    }
    
    /**
     * Output the header row
     */
    private function outputHeaders() {
        $db = get_db();
        $sets = get_db()->getTable('ElementSet')->findByRecordType('Item');
        $headers = array();
        foreach($sets as $set) {
            $elements = $db->getTable('Element')->findBySet($set->name);
            foreach($elements as $element) {
                $headers[] = $set->name . ':' . $element->name;
            }
        }
        $headers[] = 'itemType';
        $headers[] = 'collection';
        $headers[] = 'tags';
        $headers[] = 'public';
        $headers[] = 'featured';
        $headers[] = 'file';
        fputcsv($this->_file, $headers);
    }
    
    /**
     * Output the CVS for a single Item to the file
     * @param Item $item
     */
    
    private function outputItemCsv($item) {
        $sets = get_db()->getTable('ElementSet')->findByRecordType('Item');
        $csvArray = array();
        foreach($sets as $set) {
            $elements = $item->getElementsBySetName($set->name);
            foreach ($elements as $element) {
                $itemTexts = $item->getTextsByElement($element);
                if(empty($itemTexts)) {
                   $csvArray[] = null;
                } else {
                     $texts = "";
                    foreach ($itemTexts as $text) {
                        $texts .= $text->text. '^^';
                    }
                    $texts = rtrim($texts, '^^');
                    $csvArray[] = $texts;
                }
            }
        }
                
        $csvArray[] = $item->getItemType()->name;
        $csvArray[] = $item->getCollection()->name;
        $csvArray[] = $this->itemTags($item);
        $csvArray[] = $item->public;
        $csvArray[] = $item->featured;
        $csvArray[] = $this->fileUrls($item);
        fputcsv($this->_file, $csvArray);
    }
    /**
     * Return list of all the file URLs to original file
     * @param Item $item
     * @return string comma-sparated string of file Urls
     */
    
    private function fileUrls($item) {
        $urlsString = "";
        $files = $item->getFiles();
        foreach($files as $file) {
            $url = $file->getWebPath('archive') . ",";
            $urlString .= $url;
        }
        $urlString = rtrim($urlString, ",");
        return $urlString;
    }
    
    private function itemTags($item) {
        $tagString = "";
        $tags = get_db()->getTable('Tag')->findBy(array('record'=>$item));
        foreach($tags as $tag) {
            $tagString .= $tag->name . ",";
        }
        rtrim(',', $tagString);
        return $tagString;
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
     * Returns the readable name of this output format.
     *
     * @return string Human-readable name for output format
     */
    public static function getReadableName() {
        return 'CSV';
    }
    
    /**
     * Returns the HTTP content type to declare for the output format.
     *
     * @return string HTTP Content-type
     */
    public function getContentType() {
        return 'text/csv';
    }
    
    /**
     * Returns the file extension to append to the generated report.
     *
     * @return string File extension
     */
    public function getExtension() {
        return 'csv';
    }
}
