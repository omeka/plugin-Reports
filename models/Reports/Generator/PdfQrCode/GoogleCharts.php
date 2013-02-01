<?php
class Reports_Generator_PdfQrCode_GoogleCharts
{
    /**
     * The URL of the Google chart API, used to generate the QR codes
     */
    const CHART_API_URI = 'https://chart.googleapis.com/chart';

    const TEMP_FILE_PREFIX = 'omeka-reports-qr';    

    public function __construct($width, $height, $tempDir = null)
    {
        $this->_width = $width;
        $this->_height = $height;    
        $this->_tempDir = $tempDir ? $tempDir : sys_get_temp_dir();
    }
    
    /**
     * Generate a QR code for the given data.
     *
     * @return Zend_Pdf_Image
     */
    public function generate($data)
    {
        $temp = tempnam($this->_tempDir, self::TEMP_FILE_PREFIX);
        // Zend_Pdf_Image dies if lacking the correct file extension.
        $tempPng = $temp . ".png";
        rename($temp, $tempPng);
        $temp = $tempPng;
        $url = $this->_qrCodeUri($data);
        $client = new Omeka_Http_Client($url);
        $client->setMaxRetries(10);
        $response = $client->request('GET');
        if ($response->isSuccessful()) {
            file_put_contents($temp, $response->getBody());
            $image = Zend_Pdf_Image::imageWithPath($temp);
            unlink($temp);
            return $image;
        } else {
            throw new Zend_Http_Client_Exception(
                "Could not retrieve QR chart from Google."
            );
        }
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