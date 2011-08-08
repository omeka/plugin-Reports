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
     * The PDF document object
     *
     * @var Zend_Pdf
     */
    private $_pdf;
    
    /**
     * The current page in the PDF document
     *
     * @var Zend_Pdf_Page
     */
    private $_page;
    
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
    
    /**
     * Creates and generates the PDF report for the items in the report.
     *
     * @param string $filePath
     */
    public function generateReport($filePath) 
    {
        $options = unserialize($this->_reportFile->options);
        $this->_baseUrl = $options['baseUrl'];
        
        $this->_pdf = new Zend_Pdf();
        $this->_font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $this->_addPage();
        
        // Iterate through the rows and columns and draw one label per item.
        $column = 0;
        $row = 0;
        $page = 1;
        while ($items = get_db()->getTable('Item')->findBy($this->_params, 30, $page)) {
            foreach ($items as $item) {
                $this->_drawItemLabel($column, $row, $item);
                $row++;

                if($row >= self::ROWS) {
                    $column++;
                    $row = 0;
                }
                if($column >= self::COLUMNS) {
                    $this->_addPage();
                    $column = 0;
                }
            }
            $page++;
        }
        
        $this->_pdf->save($filePath);
    }
    
    /**
     * Draw one label section for one item on the PDF document.
     *
     * @param int $column Horizontal index on the current page
     * @param int $row Vertical index on the current page
     * @param Item $item The item to report on
     */
    private function _drawItemLabel($column, $row, $item)
    {
        $page = $this->_page;
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
    
    /**
     * Adds a new page to the PDF document, and switches the current page to
     * the new page.
     */
    private function _addPage()
    {
        $newPage = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
        $newPage->setFont($this->_font, self::FONT_SIZE);
        $this->_pdf->pages[] = $this->_page = $newPage;
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
    private function _drawWrappedText($text, $x, $y, $wrapWidth) 
    {
        $wrappedText = $this->_wrapText($text, $wrapWidth);
        $lines = explode("\n", $wrappedText);
        foreach($lines as $line)
        {
            $this->_page->drawText($line, $x, $y);
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
