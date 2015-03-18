<?php

// Init application
include_once(dirname(dirname(__FILE__)) . '/cc-core/system/admin.bootstrap.php');

// Verify if user is logged in
$userService = new UserService();
$adminUser = $userService->loginCheck();
Functions::RedirectIf($adminUser, HOST . '/login/');
Functions::RedirectIf($userService->checkPermissions('admin_panel', $adminUser), HOST . '/account/');

// Establish page variables, objects, arrays, etc
$message = null;
$page_title = 'Plugins';
$pluginList = array();
$invalidPluginList = array();
$installedPlugins = Plugin::getInstalledPlugins();
$enabledPlugins = Plugin::getEnabledPlugins();

// Handle "Uninstall" plugin if requested
if (!empty($_GET['uninstall'])) {
    // Validate plugin
    if (Plugin::isPluginValid($_GET['uninstall']) && Plugin::isPluginInstalled($_GET['uninstall'])) {
        
        // Load plugin
        $pluginName = $_GET['uninstall'];
        $plugin = Plugin::getPlugin($pluginName); 
        
        // Uninstall
        Plugin::uninstallPlugin($pluginName);
        $installedPlugins = Plugin::getInstalledPlugins();
        $enabledPlugins = Plugin::getEnabledPlugins();

        // Delete plugin files
        $message = $plugin->name . ' plugin has been uninstalled';
        $message_type = 'success';
        try {
            Filesystem::delete(DOC_ROOT . '/cc-content/plugins/' . $pluginName);
        } catch (Exception $e) {
            $message = $plugin->name . ' was uninstalled. However, the following errors occured during removal of plugin files. '
            . 'They need to be removed manually.<br><br>' . $e->getMessage();
            $message_type = 'errors';
        }
    }
}

// Handle "Install" plugin if requested
elseif (!empty($_GET['install'])) {
    
    // Validate plugin
    if (Plugin::isPluginValid($_GET['install']) && !Plugin::isPluginInstalled($_GET['install'])) {
        
        // Load plugin
        $pluginName = $_GET['install'];
        $plugin = Plugin::getPlugin($pluginName);
        
        // Install plugin
        Plugin::installPlugin($pluginName);
        $installedPlugins = Plugin::getInstalledPlugins();
        $enabledPlugins = Plugin::getEnabledPlugins();
        $message = $plugin->name . ' has been installed.';
        $message_type = 'success';
    }
}

// Handle "Enable" plugin if requested
elseif (!empty($_GET['enable'])) {
    // Validate plugin & enable
    if (Plugin::isPluginValid($_GET['enable'])
        && Plugin::isPluginInstalled($_GET['enable'])
        && !Plugin::isPluginEnabled($_GET['enable'])
    ) {
        $pluginName = $_GET['enable'];
        $plugin = Plugin::getPlugin($pluginName);
        Plugin::enablePlugin($pluginName);
        $enabledPlugins = Plugin::getEnabledPlugins();
        $message = $plugin->name . ' has been enabled.';
        $message_type = 'success';
    }
}

// Handle "Disable" plugin if requested
elseif (!empty($_GET['disable'])) {
    // Validate plugin & disable
    if (Plugin::isPluginValid($_GET['disable'])
        && Plugin::isPluginInstalled($_GET['disable'])
        && Plugin::isPluginEnabled($_GET['disable'])
    ) {
        $pluginName = $_GET['disable'];
        Plugin::disablePlugin($pluginName);
        $enabledPlugins = Plugin::getEnabledPlugins();
        $plugin = Plugin::getPlugin($pluginName);
        $message = $plugin->name . ' has been disabled.';
        $message_type = 'success';
    }
}

// Retrieve available plugins
foreach (glob(DOC_ROOT . '/cc-content/plugins/*') as $pluginPath) {
    $pluginName = basename($pluginPath);
    if (Plugin::isPluginValid($pluginName)) {
        $plugin = Plugin::getPlugin($pluginName);
        $pluginList[] = $plugin;
    } else {
        $invalidPluginList[] = $pluginName;
    }
}

// Output Header
$pageName = 'plugins';
include('header.php');

?>

<h1>Plugins</h1>


<?php foreach($invalidPluginList as $invalidPlugin): ?>
    <div class="message notice">Plugin "<?=$invalidPlugin?>" is invalid and cannot be loaded.</div>
<?php endforeach; ?>

<?php if ($message): ?>
<div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>


<?php if (!empty($pluginList)): ?>

    <?php foreach ($pluginList as $plugin): ?>

        <div class="block">

            <p>
                <strong><?=$plugin->name?></strong>
                <?php if (!empty($plugin->author)): ?>
                    by: <?=$plugin->author?>
                <?php endif; ?>
            </p>


            <?php if (!empty($plugin->version)): ?>
            <p><strong>Version:</strong> <?=$plugin->version?></p>
            <?php endif; ?>


            <?php if (!empty($plugin->description)): ?>
                <p><?=$plugin->description?></p>
            <?php endif; ?>

            <?php if (Plugin::isPluginInstalled($plugin->getSystemName())): ?>
                <p>
                    <?php if (Plugin::isPluginEnabled($plugin->getSystemName()) && Plugin::hasSettingsMethod($plugin)): ?>
                        <a href="<?=ADMIN?>/plugins_settings.php?plugin=<?=$plugin->getSystemName()?>">Settings</a> &nbsp;|&nbsp;
                    <?php endif; ?>

                    <?php if (Plugin::isPluginEnabled($plugin->getSystemName())): ?>
                        <a href="<?=ADMIN?>/plugins.php?disable=<?=$plugin->getSystemName()?>">Disable</a>
                    <?php else: ?>
                        <a href="<?=ADMIN?>/plugins.php?enable=<?=$plugin->getSystemName()?>">Enable</a>
                    <?php endif; ?>

                    &nbsp;|&nbsp; <a href="<?=ADMIN?>/plugins.php?uninstall=<?=$plugin->getSystemName()?>" class="delete confirm" data-confirm="This will completely uninstall and remove this plugin from your system. Do you want to proceed?">Uninstall</a>
                </p>
            <?php else: ?>
                <a href="<?=ADMIN?>/plugins.php?install=<?=$plugin->getSystemName()?>">Install</a>
            <?php endif; ?>

        </div>

    <?php endforeach; ?>

<?php else: ?>
    <div class="block"><strong>No plugins added yet.</strong></div>
<?php endif; ?>

<?php include ('footer.php'); ?>