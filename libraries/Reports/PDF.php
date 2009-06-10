<?
$chartUrl = 'http://chart.apis.google.com/chart';

Zend_Loader::loadClass('Zend_Pdf');

$pdf = new Zend_Pdf();
$pdf->pages[] = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);

$helvetica = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

$pdf->pages[0]->setFont($helvetica, 12);
$pdf->pages[0]->drawText('Sweet!', 72, 720);

$imagefile = file_put_contents(REPORTS_SAVE_DIRECTORY.'temp.png', file_get_contents($chartUrl.'?cht=qr&chl=http://omeka.org/codex&chs=300x300'));

$image = Zend_Pdf_Image::imageWithPath(REPORTS_SAVE_DIRECTORY.'temp.png');

$pdf->pages[0]->drawImage($image, 72, 500, 144, 572);

header('Content-Disposition: inline; filename=test.pdf');
header('Content-Type: application/pdf');

echo $pdf->render();

//$pdf->save(REPORTS_SAVE_DIRECTORY.'test.pdf');
