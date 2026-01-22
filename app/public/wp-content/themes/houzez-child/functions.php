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
add_action('wp_loaded', function() {
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
?>
