<?php
/**
 * Reports show view
 *
 * Provides details and shows previously-generated files for a report.
 *
 * @package Reports
 * @subpackage Views
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

$head = array('body_class' => 'reports primary',
              'title'      => "Report #$reportsreport->id");
head($head);
?>

<h1><?php echo $head['title'];?></h1>

<div id="generate-report" class="add-button">
<form action="<?php echo uri("reports/generate/$reportsreport->id"); ?>" class="add" style="background-color: #F4F3EB; color: #c50; padding:10px;">
<?php echo $this->formSelect('format', null, null, $this->formats); ?>
<?php echo $this->formSubmit('submit-generate', 'Generate a New File', array('class' => 'add', 'style' => 'color: #c50; background-color: transparent; border: none; font-size: 1.2em;')); ?>
</form>
</div>

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
<?php if (count($reportFiles) == 0) : ?>
<p>You have not yet generated any files.</p>
<?php else: ?>
<table>
<thead>
    <th>ID</th>
    <th>Date</th>
    <th>Type</th>
    <th>Status</th>
    <th></th>
    <th></th>
</thead>
<?php foreach($reportFiles as $file) : ?>
<tr>
    <td><?php echo $file->id ?></td>
    <td><?php echo $file->created ?></td>
    <td><?php echo $file->getGenerator()->getReadableName(); ?></td>
    <td><?php echo ucwords($status = $file->status); ?></td>
    <?php if ($status == Reports_File::STATUS_COMPLETED) : ?>
    <td><a href="<?php echo uri("reports/files/show/$file->id"); ?>">View file</a></td>
    <td><a href="<?php echo uri("reports/files/delete/$file->id"); ?>" class="delete">Delete file</a></td>
    <?php else: ?>
    <td></td>
    <td></td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<?php foot(); ?>
