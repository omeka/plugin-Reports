<?php

class Reports_Generator_PdfQrCode_GoogleCharts
{
    /**
     * The URL of the Google chart API, used to generate the QR codes
     */
    const CHART_API_URI = 'http://chart.apis.google.com/chart';
    

    public function __construct($width, $height)
    {
        $this->_width = $width;
        $this->_height = $height;    
    }
    
    /**
     * Generate a QR code for the given data.
     *
     * @return Zend_Pdf_Image
     */
    public function generate($data)
    {
        // FIXME: Use tempnam() for this.
        // Temporarily save the generated QR Code.
        $temp = REPORTS_SAVE_DIRECTORY. '/qrcode.png';
        // FIXME: Use Zend_Http_Client for this.
        file_put_contents($temp, file_get_contents($this->_qrCodeUri($data)));
        $image = Zend_Pdf_Image::imageWithPath($temp);
        unlink($temp);
        return $image;
    }

    /**
     * Generate a URI to a QR code for the specified item using the Google
     * Chart API.
     *
     * @param Item $item The Item object to generate a code for
     * @return string The QR Code's URI
     */
    private function _qrCodeUri($data)
    {
        $args = array(
            'cht' => 'qr',
            'chl' => $data,
            'chs' => $this->_width . 'x' . $this->_height,
        );
        return self::CHART_API_URI . '?' . http_build_query($args);
    }
    
}
