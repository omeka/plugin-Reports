<?php
/**
 * Admin page add report view
 *
 * Provides the page for creating new reports.
 *
 * @package Reports
 * @subpackage Views
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */
 
echo head(array('title' => __('Add a Report'), 'bodyclass'=>'reports'));
echo flash();
?>

<form method="post" id="report-form" action="">
    
    <section class="seven columns alpha" id="edit-form">
        <div>
            <fieldset class="set">
            <div class="field">
                <?php echo $this->form->name; ?>
            </div>
            <div class="field">
                <?php echo $this->form->description; ?>
            </div>
            </fieldset>
        </div>
    </section>
    
    <section class="three columns omega">
        <div id="save" class="panel">
            <input type="submit" class="big green button" name="submit" value="<?php echo __('Add Report'); ?>" />
            <?php //fire_plugin_hook("admin_reports_panel_buttons", array('view'=>$this, 'record'=>$report)); ?>
            <?php //fire_plugin_hook("admin_reports_panel_fields", array('view'=>$this, 'record'=>$report)); ?>
        </div>
    </section>
</form>
<?php echo foot(); ?>
