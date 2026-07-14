<?php
class Reports_Generator_PdfQrCode_Bacon
{
    const TEMP_FILE_PREFIX = 'omeka-reports-qr';    

    protected $_size;

    public function __construct($size)
    {
        $this->_size = $size;
    }
    
    /**
     * Generate a QR code for the given data.
     *
     * @return Zend_Pdf_Image
     */
    public function generate($data)
    {
        $temp = tempnam(sys_get_temp_dir(), self::TEMP_FILE_PREFIX);
        // Zend_Pdf_Image dies if lacking the correct file extension.
        $tempPng = $temp . ".png";
        rename($temp, $tempPng);
        $temp = $tempPng;

        $renderer = new BaconQrCode\Renderer\GDLibRenderer($this->_size);
        $writer = new BaconQrCode\Writer($renderer);
        $writer->writeFile($data, $temp);
        $image = Zend_Pdf_Image::imageWithPath($temp);
        unlink($temp);
        return $image;
    }
}
