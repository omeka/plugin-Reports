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
              'title'      => 'Reports | Add');
head($head);
?>

<h1><?php echo $head['title'];?></h1>

<div id="primary">

<?php echo flash(); ?>

<h2>Report Metadata</h2>
<div>
    <form method="post" action="<?php echo uri('reports/submit'); ?>">
        <div class="field">
            <?php echo $this->formLabel('name', 'Name'); ?>
            <div class="inputs">
            <?php echo $this->formText('name', null, array('size' => 60)); ?>
            <p class="explanation">Name of the report to add.</p>
            </div>
        </div>
        <div class="field">
            <?php echo $this->formLabel('description', 'Description'); ?>
            <div class="inputs">
            <?php echo $this->formTextArea('description', null, array('rows'=>'10','cols'=>'60')); ?>
            <p class="explanation">Description of the report to add.</p>
            </div>
        </div>
        
        <?php echo $this->formSubmit('submit_add_report', 'Add Report', array('class' => 'submit submit-medium')); ?>
    </form>
</div>

<?php echo items_search_form(); ?>

</div>

<?php foot(); ?>