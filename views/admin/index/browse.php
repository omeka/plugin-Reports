<?php 
$pageTitle = __('Browse Reports') . ' ' .  __('(%s total)', $total_results);
echo head(array('title'=>$pageTitle, 'bodyclass'=>'reports'));
echo flash();
?>
<?php if ($total_results > 0): ?>
    <div class="table-actions">
    <?php if (is_allowed('Reports_Index', 'add')): ?>
        <a href="<?php echo html_escape(url('reports/add')); ?>" class="small green button">
            <?php echo __('Add a Report'); ?>
        </a>
    <?php endif; ?>
    </div>
    <div class="pagination"><?php echo pagination_links(); ?></div>
    <?php if (has_loop_records('reports_reports')): ?>
        <table id="reports" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                <?php
                $sortLinks = array(
                    __('ID') => 'id',
                    __('Name') => 'name',
                    __('Creator') => 'creator',
                    __('Date Modified') => 'modified',
                    __('Items') => null,
                    __('Filter') => null,
                    __('Generate a New File') => null,
                );
                ?>
                <?php echo browse_sort_links($sortLinks, array('link_tag' => 'th scope="col"', 'list_tag' => '')); ?>
                </tr>
            </thead>
            <tbody>
                <?php $key = 0; ?>
                <?php foreach (loop('Reports_Report') as $report): ?>
                <?php
                    if ($report->query):
                        $query = http_build_query(unserialize($report->query));
                    else:
                        $query = '';
                    endif;
                ?>
                <tr class="report<?php if(++$key%2==1) echo ' odd'; else echo ' even'; ?>">
                    <td><?php echo $report->id; ?></td>
                    <td><a href="<?php echo record_url($report); ?>">
                        <?php echo html_escape($report->name); ?></a>
                    </td>
                    <td><?php echo html_escape($report->getCreatorName()); ?></td>
                    <td><?php echo format_date($report->modified); ?></td>
                    <td><a href="<?php echo url("items/browse")."?$query"; ?>"><?php echo $reportItemCounts[(string)$report->id]; ?></a></td>
                    <td><a href="<?php echo record_url($report, 'query'); ?>"><?php echo __('Edit Filter'); ?></a></td>
                    <td><form action="<?php echo record_url($report, 'generate'); ?>">
                    <?php echo $this->formSelect('format', null, array('aria-label' => __('Format')), $this->formats); ?>
                    <?php echo $this->formSubmit('submit-generate', 'Generate'); ?>
                    </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (is_allowed('Reports_Index', 'add')): ?>
            <a href="<?php echo html_escape(url('reports/add')); ?>" class="small green button"><?php echo __('Add a Report'); ?></a>
        <?php endif; ?>
    <?php else: ?>
        <p><?php echo __('There are no reports on this page.'); ?> <?php echo link_to('reports_reports', null, __('View All Reports')); ?></p>
    <?php endif; ?> 
<?php else: ?>
    <h2><?php echo __('You have no reports.'); ?></h2>
    <?php if (is_allowed('Reports_Index', 'add')): ?>
        <p><?php echo __('Get started by adding your first report.'); ?></p>
        <a href="<?php echo html_escape(url('reports/add')); ?>" class="add big green button"><?php echo __('Add a Report'); ?></a>
    <?php endif; ?>
<?php endif; ?>

<?php fire_plugin_hook('admin_reports_browse', array('reports' => $reports_reports, 'view' => $this)); ?>

<?php echo foot(); ?>
