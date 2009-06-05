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

<h2>Report Details</h2>
<div>
    <form method="post">
    <div class="field">
        <?php echo label(array('for' => 'name'),'Report Name'); ?>
        <div class="inputs">
            <?php echo text(array('name'=>'name', 'class'=>'textinput', 'id'=>'name', 'size'=>'40'), $reportsreport->name); ?>
        </div>
    <?php echo form_error('name'); ?>
    </div>

    <div class="field">
    	<?php echo label(array('for' => 'description'),'Description'); ?>
        <?php echo form_error('description'); ?>
        <div class="inputs">
            <?php echo textarea(array('name'=>'description', 'class'=>'textinput', 'id'=>'description','rows'=>'10','cols'=>'60'), $reportsreport->description); ?>
        </div>
    </div>
    <?php echo $this->formHidden('creator', Omeka_Context::getInstance()->getCurrentUser()->id); ?>
    <?php echo $this->formSubmit('submit_add_report', 'Add Report', array('class' => 'submit submit-medium')); ?>
    </form>
</div>

<?php echo items_search_form(); ?>

</div>

<?php foot(); ?>