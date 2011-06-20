<?php
/**
 * @package Reports
 * @subpackage Generators
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Abstract parent class for report generators.
 *
 * @package Reports
 * @subpackage Generators
 */
abstract class Reports_Generator
{
    const CLASS_PREFIX = 'Reports_Generator_';

    /**
     * Model object for file to be generated.
     * @var Reports_File
     */
    protected $_reportFile;
    
    /**
     * Model object for underlying report, stores name, query.
     * @var ReportsReport
     */
    protected $_report;
    
    /**
     * Search parameters for findBy() to find items.
     * @var array
     */
    protected $_params;
    
    /**
     * Generates a random filename and passes to the subclass' generateReport
     * method to create and save the report.
     * 
     * @param Reports_File $reportFile The report file to be generated
     */
    public function __construct($reportFile) {
        $this->_reportFile = $reportFile;
    }

    /**
     * Handler for PHP warnings thrown during report generation.  Adds a
     * status message to the database.
     *
     * @param int $errno Error level
     * @param string $errstr Error message string
     * @param string $errfile File where the error was raised
     * @param int $errline Line number where the error was raised
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $this->_addStatusMessage("$errstr in $errfile on $errline", 'PHP Warning');
        return true;
    }
    
    /**
     * Adds a status message to the database, with a prepended string to
     * indicate the level of severity.  Dates are automatically shown as well.
     *
     * @param string $message The message to be added
     * @param string $prepend The string to be prepended to the message
     * @param string $delimiter The delimiter between previous messages and
     *  the new message
     */
    final protected function _addStatusMessage($message, $prepend = 'Notice', $delimiter = "\n")
    {
        if (strlen($this->_reportFile->messages) == 0) {
            $delimiter = '';
        }
        $date = date('Y-m-d H:i:s O');
        $this->_reportFile->messages .= "$delimiter$date: $prepend: $message";
    }
    
    /**
     * Abstract function to generate the report itself.
     *
     * @param string $path Pathname of the file to be generated
     */
    public abstract function generateReport($path);
    
    /**
     * Returns the readable name of the subclass' output format.
     *
     * @return string Human-readable name for output format
     */
    public abstract function getReadableName();
    
    /**
     * Returns the HTTP content type to declare for the output format.
     *
     * @return string HTTP Content-type
     */
    public abstract function getContentType();
    
    /**
     * Returns the file extension to append to the generated report.
     *
     * @return string File extension
     */
    public abstract function getExtension();

    public function generate()
    {
        set_error_handler(array($this, 'errorHandler'), E_WARNING);
        try {
            $this->_reportFile->status = Reports_File::STATUS_IN_PROGRESS;
            $this->_reportFile->forceSave();
    
            $this->_report = $this->_reportFile->getReport();
            $this->_params = reports_convert_search_filters(unserialize($this->_report->query));
            
            // Creates a random filename based on the type of report.
            $filter = new Omeka_Filter_Filename();
            $filename = $filter->renameFileForArchive('report_'.$this->getExtension());
            $path = reports_save_directory() . '/' . $filename;
            // Generates the report (passes to subclass)
            $this->generateReport($path);
    
            $this->_reportFile->status = Reports_File::STATUS_COMPLETED;
            $this->_reportFile->filename = $filename;
        } catch (Exception $e) {
            $this->_reportFile->status = Reports_File::STATUS_ERROR;
            $this->_addStatusMessage($e->getMessage(), 'Error');
        }
        $this->_reportFile->forceSave();
    }

    public static function factory($type)
    {
        if ($type instanceof Reports_File) {
            $typeStr = $type->type;
            $file = $type;
        } else {
            $typeStr = $type; 
            $file = null;
        }
        $class = self::CLASS_PREFIX . $typeStr;
        // FIXME: Do not instantiate this class with a null argument
        // (unnecessary).
        return new $class($file);
    }
}
