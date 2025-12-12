<?php

if ( !defined('ABSPATH') ) exit;

use Realtyna\Core\Utilities\SettingsField;

SettingsField::input(array(
    'parent_name' => 'mls-on-the-fly-settings',
    'child_name' => 'url_patterns',
    'id' => 'mls-on-the-fly-settings-url-patterns',
    'label' => __( 'URL Pattern', 'realtyna-mls-on-the-fly' ),
    'class' => 'bold',
    'type'  => 'text',
    'description'  => '{ListingKey} is a required pattern, If you forgot it, {ListingKey} will be added to start of URL automatically',
    'value' => $settings['url_patterns'] ?? '',
));