<?php
/*
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
*/
/**
 * Admin page index view
 *
 * Provides the main landing page of the administrative interface.
 *
 * @package Reports
 * @subpackage Views
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

$head = array('body_class' => 'reports primary',
              'title'      => 'Reports');
head($head);
?>

<h1><?php echo $head['title'];?></h1>

<p id="add-report" class="add-button"><a href="<?php echo uri('reports/add'); ?>" class="add">Add a Report</a></p>

<div id="primary">

<?php echo flash(); ?>

<?php if (count($reports) == 0) : ?>
<p>You haven&apos;t created any reports yet.  <a href="<?php echo uri('reports/add'); ?>">Create one.</a></p>
<?php else : ?>
<table>
<thead>
    <th>ID</th>
    <th>Name</th>
    <th>Creator</th>
    <th>Date Added</th>
    <th>Items</th>
</thead>
<?php foreach($reports as $report) : ?>
<tr>
<td><?php echo $report['reportObject']->id; ?></td>
<td><a href="<?php echo uri('reports/show').'/'.$report['reportObject']->id ?>"><?php echo $report['reportObject']->name; ?></a></td>
<td><?php echo $report['userName']; ?></td>
<td><?php echo $report['reportObject']->modified; ?></td>
<td><?php echo 'hrm.' ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<?php foot(); ?>