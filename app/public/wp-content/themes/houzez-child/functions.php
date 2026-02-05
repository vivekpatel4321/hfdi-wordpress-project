<?php

// BCC all admins, editors, and managers to all single property page inquiries
add_filter('wp_mail', function ($args) {

    // Safety
    if (empty($args['message']) || !is_string($args['message'])) {
        return $args;
    }

    /**
     * HARD EXIT FOR HOME PAGE / DASHBOARD INQUIRIES
     * These MUST NOT be touched or AJAX breaks
     */
    // if (strpos($args['message'], '/board/?page=enquiries') !== false) {
    //     return $args;
    // }

    /**
     * Only SINGLE PROPERTY inquiries contain /property/{slug}/
     */
    if (!preg_match('#/property/[^/\s]+/#', $args['message'])) {
        return $args;
    }

    // Normalize headers ONLY for property inquiries
    if (!isset($args['headers'])) {
        $args['headers'] = [];
    } elseif (!is_array($args['headers'])) {
        $args['headers'] = [$args['headers']];
    }

    // BCC all admins, editors, and managers
    $users = get_users([
        'role__in' => ['administrator', 'editor', 'houzez_manager'],
        'fields'   => ['user_email'],
    ]);

    foreach ($users as $user) {
        if (!empty($user->user_email)) {
            $args['headers'][] = 'Bcc: ' . $user->user_email;
        }
    }

    return $args;
}, 1);

/**
 * Change "Send Message" button text to "Send Email" on property forms
 * This filters the houzez_option function output by modifying the global options array
 */
add_action('init', function() {
    global $houzez_options;
    
    // Ensure the global options array exists
    if (!isset($houzez_options)) {
        $houzez_options = get_option('houzez_options', array());
    }
    
    // Change "Send Message" to "Send Email" for the send button
    if (isset($houzez_options['spl_btn_send']) && $houzez_options['spl_btn_send'] === 'Send Message') {
        $houzez_options['spl_btn_send'] = 'Send Email';
    }
    
    // Also change for spl_btn_message if it exists
    if (isset($houzez_options['spl_btn_message']) && $houzez_options['spl_btn_message'] === 'Send Message') {
        $houzez_options['spl_btn_message'] = 'Send Email';
    }
}, 20);

// Also filter using Redux options filter if available
add_filter('redux/options/houzez_options/spl_btn_send', function($value) {
    return ($value === 'Send Message') ? 'Send Email' : $value;
}, 20);

add_filter('redux/options/houzez_options/spl_btn_message', function($value) {
    return ($value === 'Send Message') ? 'Send Email' : $value;
}, 20);


/**
 * Change the Microsoft login button color to the same as the theme color on login screen
 */
add_action('login_enqueue_scripts', function () {
    ?>
    <style>
        /* miniOrange Azure login button - default */
        body.login .moazure_login_button {
            background-color: #005AA9 !important;
            border-color: #005AA9 !important;
            color: #ffffff !important;
        }

        /* hover state */
        body.login .moazure_login_button:hover {
            background-color: #F08121 !important;
            border-color: #F08121 !important;
            color: #ffffff !important;
        }
    </style>
    <?php
});

/**
 * Change the Houzez Country taxonomy labels to 'Counties'
 */

add_action('init', function () {

    // Houzez Country taxonomy slug
    if (!taxonomy_exists('property_country')) {
        return;
    }

    global $wp_taxonomies;

    $labels = &$wp_taxonomies['property_country']->labels;

    // Plural
    $labels->name = 'Counties';
    $labels->all_items = 'All Counties';
    $labels->search_items = 'Search Counties';
    $labels->popular_items = 'Popular Counties';

    // Singular
    $labels->singular_name = 'County';
    $labels->edit_item = 'Edit County';
    $labels->update_item = 'Update County';
    $labels->add_new_item = 'Add New County';
    $labels->new_item_name = 'New County Name';

    // Menu
    $labels->menu_name = 'Counties';
});

add_filter('houzez_search_country_label', function () {
    return 'Counties';
});

add_filter('houzez_search_country_placeholder', function () {
    return 'All Counties';
});

add_filter('houzez_country_terms_args', function ($args) {
    $args['hide_empty'] = false;
    return $args;
});

// Ensure state/city/country dropdowns show empty terms (don't hide empty)
add_action('init', function() {
    global $houzez_options;

    if (!isset($houzez_options)) {
        $houzez_options = get_option('houzez_options', array());
    }

    // Set to 0 (disabled) so houzez_hide_empty_taxonomies() returns false
    $houzez_options['state_city_area_dropdowns'] = 0;
    // Set search placeholder label to 'All Counties'
    $houzez_options['srh_countries'] = 'All Counties';
}, 100);
