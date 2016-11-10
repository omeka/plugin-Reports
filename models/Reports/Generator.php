<?php
/**
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
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
     * @var Omeka_Storage
     */
    protected $_storage;

    private $_storagePrefixDir;
    
    /**
     * Generates a random filename and passes to the subclass' generateReport
     * method to create and save the report.
     *
     * @param Reports_File $reportFile The report file to be generated
     */
    public function __construct(
        $reportFile,
        $options = array()
    ) {
        $this->_reportFile = $reportFile;
        $this->setOptions($options);
    }

    final public function setOptions(array $options)
    {
        foreach ($options as $k => $v) {
            $method = 'set' . ucwords($k);
            if (method_exists($this, $method)) {
                $this->$method($v);
            }
        }
    }

    public function getStoragePrefixDir()
    {
        return $this->_storagePrefixDir;
    }

    public function setStoragePrefixDir($prefixDir)
    {
        $this->_storagePrefixDir = $prefixDir;
    }

    public function setStorage($storage)
    {
        $this->_storage = $storage;
    }
    
    public function getStorage()
    {
        return $this->_storage;
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
        $msg = "$errstr in $errfile on $errline";
        $this->_addStatusMessage($msg, 'PHP Warning');
        _log($msg);
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
            $this->_reportFile->save();
    
            $this->_report = $this->_reportFile->getReport();
            $this->_params = unserialize($this->_report->query);
            
            // Creates a random filename based on the type of report.
            $filter = new Omeka_Filter_Filename();
            $filename = $filter->renameFile(
                'report.' . $this->getExtension()
            );

            // Generates the report (passes to subclass)
            $tempFilePath = tempnam($this->_storage->getTempDir(), 'reports');
            $tempFilePath = $this->generateReport($tempFilePath);
            
            // Store the report
            $destPath = $this->_storagePrefixDir . '/'  . $filename;
            $this->_storage->store($tempFilePath, $destPath);
    
            $this->_reportFile->status = Reports_File::STATUS_COMPLETED;
            $this->_reportFile->filename = $filename;
        } catch (Exception $e) {
            $this->_reportFile->status = Reports_File::STATUS_ERROR;
            $this->_addStatusMessage('storagePrefixDir: ' . $this->_storagePrefixDir . ' filename: ' . $filename . ' error:' . $e->getMessage(), 'Error');
            _log($e, Zend_Log::ERR);
        }
        $this->_reportFile->save();
    }

    public function deleteFile($filename) 
    {
        $destPath = $this->_storagePrefixDir . '/' . $filename;
        $this->_storage->delete($destPath);
    }
    
    /**
     * Returns whether the report can be stored in the associated Omeka_Storage object
     *
     * @param array &$errors The array of errors for why a file cannot store
     * @return bool whether the report can be stored in the associated Omeka_Storage object  
     */
    public function canStore(&$errors)
    {
        // this is a hack because Omeka_Storage_Adapter_Filesystem
        // does not correctly implement canStore when the localDir 
        // has been changed. It also offers no way to remove the subDirs
        // we don't use or care about.  
        // So we must directly test whether the directory we plan to write to is writeable
        $ad = $this->_storage->getAdapter();
        if ($ad instanceof Omeka_Storage_Adapter_Filesystem) {
            $opt = $ad->getOptions();
            $localDir = $opt['localDir'];
            $absPath = $localDir . '/' . $this->_storagePrefixDir; // this reimplements Omeka_Storage_Adapter_Filesystem::_getAbsPath
            $result = is_writable($absPath);
            if (!$result) {
                if (!$errors) {
                    $errors = array();
                }
                $errors[] = __("Make sure that %s is exists and is writeable.", $absPath);
            }
            return $result;
        }
        
        return $this->_storage->canStore();
    }

    public static function factory($reportFile)
    {
        $class = self::CLASS_PREFIX . $reportFile->type;
        $options = reports_get_config();
        $inst = new $class($reportFile, $options);
        $inst->setStorage(Zend_Registry::get('storage'));
        return $inst;
    }

    public static function getFormats($fromDir)
    {
        $dir = new DirectoryIterator($fromDir);
        $formats = array();
        foreach ($dir as $entry) {
            if ($entry->isFile() && !$entry->isDot()) {
                $filename = $entry->getFilename();
                if (preg_match('/^(.+)\.php$/', $filename, $match)
                    && $match[1] != 'Abstract'
                ) {
                    $className = self::CLASS_PREFIX . $match[1];
                    if (!method_exists($className, 'getReadableName')) {
                        throw new InvalidArgumentException(
                            "Invalid report type: {$match[1]}."
                        );
                    }
                    $name = call_user_func(array($className, 'getReadableName'));
                    $formats[$match[1]] = $name;
                }
            }
        }
        return $formats;
    }
}
