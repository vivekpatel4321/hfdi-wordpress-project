<?php


use Realtyna\Core\Utilities\SettingsField;
use Realtyna\MlsOnTheFly\Boot\App;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Interfaces\IntegrationInterface;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Targets\CrocoBlockIntegration;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Targets\EPLIntegration;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Targets\HouzezIntegration;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Targets\InspiryThemesIntegration;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Targets\PropertyDriveIntegration;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Targets\ToolsetIntegration;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Targets\WPLIntegration;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Targets\RealtynaIntegration;


if (!defined('ABSPATH')) {
    exit;
}

SettingsField::input(array(
    'parent_name' => 'mls-on-the-fly-settings',
    'child_name' => 'cache_time',
    'id' => 'mls-on-the-fly-settings-cache-time',
    'label' => __('Cache Timeout', 'realtyna-mls-on-the-fly'),
    'type' => 'number',
    'value' => $settings['cache_time'] ?? '',
    'min' => 60,
));



if (App::has(IntegrationInterface::class)) {
    $activeIntegration = App::get(IntegrationInterface::class);

    // Display a message about the active integration
    echo '<p><b>';
    echo __('The active integration detected is: ', 'realtyna-mls-on-the-fly') . $activeIntegration->name;
    echo '</b></p>';

    // Display a message to inform the user that they can override this option
    echo '<p>';
    echo __('If you want to override this option, you can use the select box below.', 'realtyna-mls-on-the-fly');
    echo '</p>';
}

$integrations = [
    'auto' => 'Auto Detect',
    WPLIntegration::class => 'WPL',
    EPLIntegration::class => 'EPL',
    HouzezIntegration::class => 'Houzez',
    ToolsetIntegration::class => 'Toolset',
    CrocoBlockIntegration::class => 'CrocoBlock',
    PropertyDriveIntegration::class => 'PropertyDrive',
    InspiryThemesIntegration::class => 'InspiryThemes',
    RealtynaIntegration::class => 'RealtynaSDK',
];
if (isset($settings['default_integration'])) {
    $settings['default_integration'] = str_replace('\\\\', '\\', $settings['default_integration']);
}

SettingsField::select(array(
    'parent_name' => 'mls-on-the-fly-settings',
    'child_name' => 'default_integration',
    'id' => 'mls-on-the-fly-settings-default-integration',
    'label' => __('Default Integration', 'realtyna-mls-on-the-fly'),
    'options' => $integrations,
    'value' => $settings['default_integration'] ?? '',
));

// Add SupportAI iframe
echo '<div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">';
echo '<h2><a name="chatbot">' . __('Support Chatbot', 'realtyna-mls-on-the-fly') . '</a></h2>';
echo '<iframe src="https://chat.supportai.com/651a597a1e7ddf3/" width="100%" style="height: 100%; min-height: 700px" frameborder="0"></iframe>';
echo '</div>';

