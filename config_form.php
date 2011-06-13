<?php 
/**
 * Config form include
 *
 * Included in the configuration page for the plugin to change settings.
 *
 * @package Reports
 * @copyright Copyright (c) 2009 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
?>
<div class="field">
    <label for="reports_save_directory">Reports save directory</label>
    <?php echo __v()->formText('reports_save_directory', $saveDirectory);?>
    <p class="explanation">The directory on the server where generated reports 
    will be saved.  This directory must be writable by the web server for 
    reporting to function.</p>
</div>
