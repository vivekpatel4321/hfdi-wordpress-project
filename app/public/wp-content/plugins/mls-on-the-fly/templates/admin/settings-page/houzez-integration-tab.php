<?php


use Realtyna\Core\Utilities\SettingsField;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Targets\HouzezIntegration;

if ( !defined('ABSPATH') ) exit;
$users = [];
foreach (get_users() as $user) {
    $users[$user->ID] = $user->display_name;
}

$args = array(
    'post_type' => 'houzez_agent',
    'posts_per_page' => -1 // To retrieve all posts
);

$query = new WP_Query($args);
$agents = [];

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $agents[get_the_ID()] = get_the_title();
    }
    wp_reset_postdata(); // Reset post data to prevent conflicts with other queries
}else{
    $newAgent = HouzezIntegration::addAgent('Real Estate Agent');
    $agents[$newAgent['id']] = $newAgent['name'];
}

$args = array(
    'post_type' => 'houzez_agency',
    'posts_per_page' => -1 // To retrieve all posts
);

$query = new WP_Query($args);
$agencies = [];

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $agencies[get_the_ID()] = get_the_title();
    }
    wp_reset_postdata(); // Reset post data to prevent conflicts with other queries
}else{
    $newAgency = HouzezIntegration::addAgency('Real Estate Agency');
    $agencies[$newAgency['id']] = $newAgency['name'];
}


$types = [
    'none' => 'Leave blank',
    'agent_info' => 'Show Agent Info',
    'agency_info' => 'Show Agency Info',
];

SettingsField::select(array(
    'parent_name' => 'mls-on-the-fly-settings',
    'child_name' => 'houzez_default_post_author',
    'id' => 'mls-on-the-fly-settings-houzez-default-post-author',
    'label' => __( 'Default Post author', 'realtyna-mls-on-the-fly' ),
    'options' => $users,
    'value' => $settings['houzez_default_post_author'] ?? '',
));

SettingsField::select(array(
    'parent_name' => 'mls-on-the-fly-settings',
    'child_name' => 'houzez_default_property_contact_type',
    'id' => 'mls-on-the-fly-settings-houzez-default-property-contact-type',
    'label' => __( 'Contact Information', 'realtyna-mls-on-the-fly' ),
    'options' => $types,
    'value' => $settings['houzez_default_property_contact_type'] ?? '',
));

SettingsField::select(array(
    'parent_name' => 'mls-on-the-fly-settings',
    'child_name' => 'houzez_default_property_agent',
    'id' => 'mls-on-the-fly-settings-houzez-default-property-agent',
    'label' => __( 'Default Agent', 'realtyna-mls-on-the-fly' ),
    'options' => $agents,
    'value' => $settings['houzez_default_property_agent'] ?? '',
));

SettingsField::select(array(
    'parent_name' => 'mls-on-the-fly-settings',
    'child_name' => 'houzez_default_property_agency',
    'id' => 'mls-on-the-fly-settings-houzez-default-property-agency',
    'label' => __( 'Default Agency', 'realtyna-mls-on-the-fly' ),
    'options' => $agencies,
    'value' => $settings['houzez_default_property_agency'] ?? '',
));