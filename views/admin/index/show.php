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
<h2>Report Metadata</h2>
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
<td><?php echo $reportsreport->creator; ?></td>
</tr>
<th>Date Added</th>
<td><?php echo $reportsreport->modified; ?></td>
</tr>
</table>

<h2>Generated Reports</h2>

</div>

<?php foot(); ?>