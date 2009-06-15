<?php
/**
 * Admin page add report view
 *
 * Provides the page for creating new reports.
 *
 * @package Reports
 * @subpackage Views
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

$head = array('body_class' => 'reports primary',
              'title'      => 'Reports | Edit Filter');
head($head);
?>

<h1><?php echo $head['title'];?></h1>

<div id="primary">

<?php echo flash(); ?>

<h2>Report Filter</h2>

<?php echo items_search_form(array(), current_uri()); ?>

</div>

<?php foot(); ?>