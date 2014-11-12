<?php
/**
 * Admin page edit filter view
 *
 * Provides the page for editing the filter for a report.
 *
 * @package Reports
 * @subpackage Views
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

$head = array('body_class' => 'reports content',
              'title'      => __("Edit Filter for '%s' | Reports", $reportsreport->name));
echo head($head);
echo flash();
?>

<?php echo items_search_form(array(), current_url()); ?>

<?php echo foot(); ?>
