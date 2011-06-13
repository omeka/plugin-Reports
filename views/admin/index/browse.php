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
    <th>Date Modified</th>
    <th>Items</th>
    <th>Filter</th>
    <th>Generate</th>
</thead>
<?php foreach($reports as $report) : 
      $reportObject = $report['reportObject'];
      if($reportObject->query) { 
          $query = http_build_query(unserialize($reportObject->query)); 
      } ?>
<tr>
<td><?php echo $reportObject->id; ?></td>
<td><a href="<?php echo uri("reports/index/show/$reportObject->id"); ?>"><?php echo $reportObject->name; ?></a></td>
<td><?php echo $report['userName']; ?></td>
<td><?php echo $reportObject->modified; ?></td>
<td><a href="<?php echo uri("items/browse")."?$query"; ?>"><?php echo $report['count']; ?></a></td>
<td><a href="<?php echo uri("reports/index/query/$reportObject->id"); ?>">Edit filter</a></td>
<td><form action="<?php echo uri("reports/index/generate/$reportObject->id"); ?>">
<?php echo $this->formSelect('format', null, null, $this->formats); ?>
<?php echo $this->formSubmit('submit-generate', 'Generate'); ?>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<?php foot(); ?>
