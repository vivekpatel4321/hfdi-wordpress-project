<?php
use Realtyna\Core\Utilities\SettingsField;

if ( !defined('ABSPATH') ) exit;

$checked = isset($settings['show_raw_data']) && $settings['show_raw_data'];
SettingsField::checkbox(array(
    'parent_name' => 'mls-on-the-fly-settings',
    'child_name' => 'show_raw_data',
    'id' => 'mls-on-the-fly-settings-show-raw-data',
    'label' => __( 'Enable Developer Mode', 'realtyna-mls-on-the-fly' ),
    'description'  => 'Enable debug features (developers only), keep this unchecked in production to prevent potential errors.',
    'value' => 'yes',
    'checked' => $checked,
));
