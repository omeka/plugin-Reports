<?php
/**
 * @package Reports
 * @subpackage Generators
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Report generator for PDF output with QR Codes included.
 *
 * Note that the PDF coordinate system starts from the bottom-left corner
 * of the page, and all measurements are in points (1/72 of an inch).
 *
 * @package Reports
 * @subpackage Generators
 */
class Reports_Generator_PdfQrCode 
    extends Reports_Generator
    implements Reports_GeneratorInterface
{
    /**
     * The current font being used by the PDF document
     *
     * @var Zend_Pdf_Resource_Font
     */
    private $_font;
    
    /**
     * The base URL of the Omeka installation that spawned the report
     *
     * @var string
     */
    private $_baseUrl;
    
    private $_qrGenerator;

    /**
     * The page count when retrieving items from the database. 
     *
     * @var integer
     */
    private $_itemPageNum = 1;

    private $_pdfPageCount = 0;

    /**
     * Set of strings corresponding to the path of each intermediate report 
     * file. 
     *
     * @var array
     */
    private $_filePaths = array();

    // Spacing constants for 5163 labels, in points.
    
    const PAGE_HEIGHT = 792;
    const PAGE_WIDTH = 612;
    
    const MARGIN_LEFT = 13.5;
    const MARGIN_RIGHT = 36;
    const MARGIN_TOP = 36;
    const MARGIN_BOTTOM = 13.5;
    
    const COLUMNS = 2;
    const ROWS = 5;
    
    const HORIZONTAL_SPACING = 11.25;
    const VERTICAL_SPACING = 0;
    
    const LABEL_HEIGHT = 144;
    const LABEL_WIDTH = 288;
    
    const FONT_SIZE = 10;

    const QR_HEIGHT = 300;
    const QR_WIDTH = 300;

    const PDF_PAGES_PER_FILE = 10;
    
    /**
     * Creates and generates the PDF report for the items in the report.
     *
     * @param string $filePath The path to the zip that contains the generated
     * PDFs.
     */
    public function generateReport($filePath) 
    {
        if (!extension_loaded('zip')) {
            throw new RuntimeException("zip extension is required to bundle "
                . "report PDF files.");
        }
        $options = unserialize($this->_reportFile->options);
        $this->_baseUrl = $options['baseUrl'];
        $this->_font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        $fileSuffix = 1;
        $pdfPath = $this->_newPdf($filePath, $fileSuffix);
        while ($items = $this->_getItems()) {
            if ($this->_pdfPageCount >= self::PDF_PAGES_PER_FILE) {
                $fileSuffix++;
                $pdfPath = $this->_newPdf($filePath, $fileSuffix);
                _log("Created new PDF file '$pdfPath'");
                $this->_pdfPageCount = 0;
            }
            $this->_addItems($items, $pdfPath);
        }
        $filePath .= '.zip';
        $this->_zipFiles($filePath);
        return $filePath;
    }

    private function _zipFiles($zipPath)
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZIPARCHIVE::CM_PKWARE_IMPLODE) !== true) {
            throw new RuntimeException("zlib cannot create '$zipPath'.");
        }
        foreach ($this->_filePaths as $path) {
            if (preg_match('/.*\-(part.+\.pdf)/', $path, $matches)) {
                $toFilename = $matches[1];
            } else {
                $toFilename = basename($path);
            }
            $zip->addFile($path, $toFilename);
            _log("Added '$toFilename' to zip '$zipPath'.");
        }
        _log("Closing zip file.");
        $zip->close();        
        _log(memory_get_peak_usage());
    }

    private function _newPdf($filePath, $fileSuffix)
    {
        $pdfPath = $this->_getTempFilename($filePath, $fileSuffix);
        $pdf = new Zend_Pdf();
        $pdf->save($pdfPath);
        $this->_filePaths[] = $pdfPath;
        return $pdfPath;
    }

    private function _getTempFilename($prefix, $count)
    {
        return $prefix . '-part' . $count . '.pdf';
    }

    private function _getItems()
    {
        $items = get_db()
            ->getTable('Item')
            ->findBy(
                $this->_params, 
                30, // TODO: Convert me to a constant.
                $this->_itemPageNum
            );
        $this->_itemPageNum++;
        return $items;
    }
    
    /**
     * Draw one label section for one item on the PDF document.
     *
     * @param int $column Horizontal index on the current page
     * @param int $row Vertical index on the current page
     * @param Item $item The item to report on
     */
    private function _drawItemLabel(
        Zend_Pdf_Page $page, 
        $column, 
        $row, 
        $item
    ) {
        // Start at the bottom left corner and count over for columns and down 
        // for rows.
        $originX = self::MARGIN_LEFT 
            + ($column * (self::LABEL_WIDTH + self::HORIZONTAL_SPACING));
        $originY = self::PAGE_HEIGHT - self::MARGIN_TOP 
            - (($row + 1) * (self::LABEL_HEIGHT + self::VERTICAL_SPACING));
        
        $page->saveGS();
        
        // Clip on label boundaries to stop text from running over.
        $page->clipRectangle(
            $originX, 
            $originY, 
            $originX + self::LABEL_WIDTH, 
            $originY + self::LABEL_HEIGHT
        );
        
        $image = $this->_getQrCode($this->_baseUrl . '/items/show/' . $item->id);
        $page->drawImage(
            $image, 
            $originX, 
            $originY, 
            $originX + self::LABEL_HEIGHT, 
            $originY + self::LABEL_HEIGHT
        );
        $titles = $item->getElementTextsByElementNameAndSetName(
            'Title', 'Dublin Core');
        if(count($titles) > 0) {
            $textOriginX = $originX + self::LABEL_HEIGHT;
            $textOriginY = $originY + (0.8 * self::LABEL_HEIGHT) ;
            $cleanTitle = strip_tags(htmlspecialchars_decode($titles[0]->text));
            $this->_drawWrappedText(
                $page,
                $cleanTitle, 
                $textOriginX, 
                $textOriginY, 
                self::LABEL_WIDTH - (self::LABEL_HEIGHT + 4)
            );   
        }
        
        // Remove clipping rectangle
        $page->restoreGS();
        
        // Release objects after use to keep memory usage down
        release_object($item);
    }

    private function _addItems($items, $filePath) {
        // To conserve memory on big jobs, the PDF should be saved 
        // incrementally after the initial page has been added. This has the 
        // additional side effect of producing a partial report in the event
        // of an error.
        $updateOnly = true;
        $itemsPerPage = self::ROWS * self::COLUMNS;

        // Reloading the PDF file (as opposed to reusing the initial 
        // object) also saves on memory, albeit inexplicably so.
        $pdf = Zend_Pdf::load($filePath);
        // Split the array into groups with just enough to fit on each 
        // page.
        $itemChunks = array_chunk($items, $itemsPerPage);
        foreach ($itemChunks as $chunk) {
            _log("Adding page for a new chunk.");
            $page = $this->_addPage($pdf);
            $column = 0;
            $row = 0;
            foreach ($chunk as $item) {
                $this->_drawItemLabel($page, $column, $row, $item);
                $row++;

                if($row >= self::ROWS) {
                    $column++;
                    $row = 0;
                }
                if($column >= self::COLUMNS) {
                    $column = 0;
                }
            }
        }
        $pdf->save($filePath, $updateOnly);
        _log(memory_get_peak_usage());
    }
    
    /**
     * Adds a new page to the PDF document.
     *
     * @return Zend_Pdf_Page
     */
    private function _addPage(Zend_Pdf $pdf)
    {
        $newPage = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
        $newPage->setFont($this->_font, self::FONT_SIZE);
        $pdf->pages[] = $newPage;
        $this->_pdfPageCount++;
        return $newPage;
    }
    
    /**
     * Wraps text on word boundaries and draws resulting text in consecutive
     * lines down from the origin point.
     *
     * @param string $text Text to draw
     * @param int $x X coordinate of origin on page
     * @param int $y Y coordinate of origin on page
     * @param int $wrapWidth Maximum width of a line
     */
    private function _drawWrappedText(
        Zend_Pdf_Page $page, 
        $text, 
        $x, 
        $y, 
        $wrapWidth
    ) {
        $wrappedText = $this->_wrapText($text, $wrapWidth);
        $lines = explode("\n", $wrappedText);
        foreach($lines as $line)
        {
            $page->drawText($line, $x, $y);
            $y -= self::FONT_SIZE + 5;
        }
    }
    
    /**
     * Returns text with newlines given a maximum width in points.
     *
     * @param string $text Text to wrap
     * @param int $wrapWidth Maximum width of a line
     * @return string Original text with newline characters inserted
     */
    private function _wrapText($text, $wrapWidth)
    {
        $wrappedText = '';
        $words = explode(' ', $text);
        $wrappedLine = '';
        foreach ($words as $word)
        {
            // if adding a new word isn't wider than $wrapWidth, add the
            // word to the line
            $wrappedWord = empty($wrappedLine) ? $word : " $word";
            $stringWidth = $this->_widthForStringUsingFontSize(
                $wrappedLine . $wrappedWord, 
                $this->_font, 
                self::FONT_SIZE
            );
            if ($stringWidth < $wrapWidth) {
                $wrappedLine .= $wrappedWord;
            } else {
                if (empty($wrappedLine)) {
                    $wrappedText .= "$word\n";
                    $wrappedLine = ''; 
                } else {
                    $wrappedText .= "$wrappedLine\n";
                    $wrappedLine = $word;
                }
            }
        }
        $wrappedText .= $wrappedLine;
        return $wrappedText;
    }
    
    /**
     * Returns the total width in points of the string using the specified
     * font and size.
     *
     * @link http://devzone.zend.com/article/2525
     * @param string $string
     * @param Zend_Pdf_Resource_Font $font
     * @param float $fontSize Font size in points
     * @return float
     */
    private function _widthForStringUsingFontSize($string, $font, $fontSize)
    {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) |
                             ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }

    private function _getQrCode($uri)
    {
        if (!$this->_qrGenerator) {
            $this->_qrGenerator = new Reports_Generator_PdfQrCode_GoogleCharts(
                self::QR_WIDTH,
                self::QR_HEIGHT
            );
        }
        return $this->_qrGenerator->generate($uri);
    }

    /**
     * Returns the readable name of this output format.
     *
     * @return string Human-readable name for output format
     */
    public static function getReadableName() {
        return 'QR Code (PDF)';
    }
    
    /**
     * Returns the HTTP content type to declare for the output format.
     *
     * @return string HTTP Content-type
     */
    public function getContentType() {
        return 'application/pdf';
    }
    
    /**
     * Returns the file extension to append to the generated report.
     *
     * @return string File extension
     */
    public function getExtension() {
        return 'pdf';
    }
}
