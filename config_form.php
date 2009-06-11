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
    <label for="reports_php_path">Path to PHP-CLI</label>
    <?php echo __v()->formText('reports_php_path', $phpPath);?>
    <p class="explanation">Path to PHP-CLI.  The path must point to a 
    commnand-line PHP 5 binary.  Check with your web host for more 
    information.</p>
</div>
<div class="field">
    <label for="reports_save_directory">Reports save directory</label>
    <?php echo __v()->formText('reports_save_directory', $saveDirectory);?>
    <p class="explanation">The directory on the server where generated reports 
    will be saved.  This directory must be writable by the web server for 
    reporting to function.</p>
</div>
