<?php
/**
 * Reports show view
 *
 * Provides details and shows previously-generated files for a report.
 *
 * @package Reports
 * @subpackage Views
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

$head = array('body_class' => 'reports content',
              'title'      => __('Report #%s | Reports', $report->id));
echo head($head);
?>
<?php echo common('reports-nav'); ?>

<?php echo flash(); ?>

<div id="generate-report" class="add-button">
<form action="<?php 
echo url(
    array(
        'controller' => 'index',
        'action' => 'generate',
        'id' => $report->id,
    ),
    'default'
); ?>" class="add" style="background-color: #F4F3EB; color: #c50; padding-right:10px;">
<?php echo $this->formSelect('format', null, null, $this->formats); ?>
<?php echo $this->formSubmit('submit-generate', __('Generate a New File'), array('class' => 'add', 'style' => 'float:none; display:inline;')); ?>
</form>
</div>

<h2><?php echo __('Report Details'); ?></h2>
<table>
<tr>
<th><?php echo __('Name'); ?></th>
<td><?php echo $report->name; ?></td>
</tr>
<tr>
<th><?php echo __('Description'); ?></th>
<td><?php echo $report->description; ?></td>
</tr>
<th><?php echo __('Creator'); ?></th>
<td><?php echo html_escape($report->getCreatorName()); ?></td>
</tr>
<th><?php echo __('Date Added'); ?></th>
<td><?php echo format_date($report->modified); ?></td>
</tr>
</table>

<h2><?php echo __('Generated Files'); ?></h2>
<?php if (count($reportFiles) == 0) : ?>
<p><?php echo __('You have not yet generated any files.'); ?></p>
<?php else: ?>
<table>
<thead>
    <th><?php echo __('ID'); ?></th>
    <th><?php echo __('Date'); ?></th>
    <th><?php echo __('Type'); ?></th>
    <th><?php echo __('Status'); ?></th>
    <th></th>
    <th></th>
</thead>
<?php foreach($reportFiles as $file) : ?>
<tr>
    <td><?php echo $file->id ?></td>
    <td><?php echo format_date($file->created, Zend_Date::DATETIME_MEDIUM); ?></td>
    <td><?php echo $file->getGenerator()->getReadableName(); ?></td>
    <td><?php echo ucwords($status = $file->status); ?></td>
    <?php if ($status == Reports_File::STATUS_COMPLETED) : ?>
    <td><a href="<?php echo html_escape($file->getUrl()); ?>"><?php echo __('Download File'); ?></a></td>
    <td><a href="<?php 
echo url(
    array(
        'controller' => 'files',
        'action' => 'delete-confirm',
        'id' => $file->id,
    )
); ?>" class="delete-confirm"><?php echo __('Delete File'); ?></a></td>
    <?php else: ?>
    <td></td>
    <td></td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<?php echo foot(); ?>
