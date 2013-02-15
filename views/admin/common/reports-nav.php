<nav id="section-nav" class="navigation vertical">
<?php
    $navArray = array(
        array(
            'label' => 'Reports',
            'module' => 'reports',
            'action' => 'index',
        ),
    );
    echo nav($navArray, 'admin_navigation_settings');
?>
</nav>