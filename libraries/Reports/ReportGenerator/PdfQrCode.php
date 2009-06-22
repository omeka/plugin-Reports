<?php

class Reports_ReportGenerator_PdfQrCode extends Reports_ReportGenerator
{
    private $_file;
    private $_items;
    
    private $_pdf;
    private $_page;
    private $_font;
    
    private $_baseUrl;
    
    const CHART_API_URI = 'http://chart.apis.google.com/chart';
    
    // Spacing constants for 5160 labels, in points.
    
    const PAGE_HEIGHT = 792;
    const PAGE_WIDTH = 612;
    
    const MARGIN_LEFT = 13.5;
    const MARGIN_RIGHT = 36;
    const MARGIN_TOP = 36;
    const MARGIN_BOTTOM = 13.5;
    
    const COLUMNS = 3;
    const ROWS = 10;
    
    const HORIZONTAL_SPACING = 11.25;
    const VERTICAL_SPACING = 0;
    
    const LABEL_HEIGHT = 72;
    const LABEL_WIDTH = 189;
    
    const FONT_SIZE = 10;
    
    public function generateReport($filename) {
        error_reporting(E_ALL);
        $this->_items = get_db()->getTable('Item')->findBy($this->_params);
        
        $options = unserialize($this->_reportFile->options);
        $this->_baseUrl = $options['baseUrl'];
        
        $this->_pdf = new Zend_Pdf();
        $this->_font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $this->_addPage();
        
        // Iterate through the rows and columns and draw one label per item.
        $column = 0;
        $row = 0;
        foreach ($this->_items as $item) {
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
        
        $this->_pdf->save($filename);
    }
    
    private function _qrCodeUri($item)
    {
        $args = array('cht' => 'qr',
                      'chl' => $this->_baseUrl.'/items/show/'.$item->id,
                      'chs' => '300x300');
        return self::CHART_API_URI.'?'.http_build_query($args);
    }
    
    private function _drawItemLabel($column, $row, $item)
    {
        $page = $this->_page;
        // Start at the bottom left corner and count over for columns and down for rows.
        $originX = self::MARGIN_LEFT + ($column * (self::LABEL_WIDTH + self::HORIZONTAL_SPACING));
        $originY = 792 - self::MARGIN_TOP - (($row + 1) * (self::LABEL_HEIGHT + self::VERTICAL_SPACING));
        
        $page->saveGS();
        
        // Clip on label boundaries to stop text from running over.
        $page->clipRectangle($originX, $originY, $originX + self::LABEL_WIDTH, $originY + self::LABEL_HEIGHT);
        
        // Temporarily save the generated QR Code.
        $temp = REPORTS_SAVE_DIRECTORY. '/qrcode.png';
        file_put_contents($temp, file_get_contents($this->_qrCodeUri($item)));
        $image = Zend_Pdf_Image::imageWithPath($temp);
        unlink($temp);
        
        $page->drawImage($image, $originX, $originY, $originX + 72, $originY + 72);
        $titles = $item->getElementTextsByElementNameAndSetName('Title', 'Dublin Core');
        if(count($titles) > 0)
        //$page->drawText($titles[0]->text, $originX + 72, $originY + 50);
        $textOriginX = $originX + 72;
        $textOriginY = $originY + 55;
        $this->_drawWrappedText($titles[0]->text, $textOriginX, $textOriginY, 127);
        
        $page->restoreGS();
    }
    
    /**
     * Adds a new page to the PDF document, and switches the current page to
     * the new page.
     * 
     * @param $pdf Zend_Pdf PDF document.
     */
    private function _addPage()
    {
        $newPage = $this->_pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
        $newPage->setFont($this->_font, self::FONT_SIZE);
        $this->_pdf->pages[] = $this->_page = $newPage;
    }
    
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
            if ($this->_widthForStringUsingFontSize($wrappedLine.$wrappedWord, $this->_font, self::FONT_SIZE) < $wrapWidth) {
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
    * Returns the total width in points of the string using the specified font
    * and size.
    *
    * This is not the most efficient way to perform this calculation. I'm
    * concentrating optimization efforts on the upcoming layout manager class.
    * Similar calculations exist inside the layout manager class, but widths
    * are generally calculated only after determining line fragments.
    *
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