<?php
/**
 * Admin page edit filter view
 *
 * Provides the page for editing the filter for a report.
 *
 * @package Reports
 * @subpackage Views
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

$head = array('body_class' => 'reports primary',
              'title'      => "Report '$reportsreport->name' | Edit Filter");
head($head);
?>

<h1><?php echo $head['title'];?></h1>

<script type="text/javascript" charset="utf-8">
    Event.observe(window, 'load', Omeka.Search.activateSearchButtons);
</script>

<div id="primary">

<?php echo flash(); ?>

<h2>Report Filter</h2>

<?php echo items_search_form(array(), current_uri()); ?>

</div>

<?php foot(); ?>
