<?php
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
              'title'      => "Report #$reportsreport->id");
head($head);
?>

<h1><?php echo $head['title'];?></h1>

<div id="primary">

<?php echo flash(); ?>
<h2>Report Details</h2>
<table>
<tr>
<th>Name</th>
<td><?php echo $reportsreport->name; ?></td>
</tr>
<tr>
<th>Description</th>
<td><?php echo $reportsreport->description; ?></td>
</tr>
<th>Creator</th>
<td><?php echo reports_getNameForEntityId($reportsreport->creator); ?></td>
</tr>
<th>Date Added</th>
<td><?php echo $reportsreport->modified; ?></td>
</tr>
</table>

<h2>Generated Files</h2>
<p><a href="<?php echo uri("reports/generate/$reportsreport->id"); ?>">Generate a new file.</a></p>
<form action="<?php echo uri("reports/generate/$reportsreport->id"); ?>">
<?php echo $this->formSelect('format', null, null, $this->formats); ?>
</form>
<?php if (count($reportFiles) == 0) : ?>
<p>You have not generated any files.</p>
<?php else: ?>
<table>
<thead>
    <th>ID</th>
    <th>Date</th>
    <th>Type</th>
    <th>Status</th>
    <th>View</th>
</thead>
<?php foreach($reportFiles as $file) : ?>
<tr>
    <td><?php echo $file->id ?></td>
    <td><?php echo $file->created ?></td>
    <td><?php echo $file->type ?></td>
    <td><?php echo $file->status ?></td>
    <td><a href="<?php echo uri("reports/files/show/$file->id"); ?>">View file</a></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<?php foot(); ?>