<?php

namespace Realtyna\MlsOnTheFly\Components\SupportAI;

use Realtyna\Core\Abstracts\ComponentAbstract;

class SupportAIComponent extends ComponentAbstract
{
    public function register(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueSupportIconStyles']);
        add_action('admin_footer', [$this, 'addSupportIcon']);
    }

    public function enqueueSupportIconStyles(): void
    {
        $screen = get_current_screen();
        
        // Check if we are on any of the plugin's admin pages using wildcard
        if ($screen && ($screen->id != 'mls-on-the-fly_page_mls-on-the-fly-settings') && str_contains($screen->id, 'mls-on-the-fly')) {
            wp_enqueue_style(
                'support-icon-styles',
                plugins_url('assets/css/support-icon.css', __FILE__)
            );
        }
    }

    public function addSupportIcon(): void
    {
        $screen = get_current_screen();
        
        // Check if we are on any of the plugin's admin pages using wildcard
        if ($screen && str_contains($screen->id, 'mls-on-the-fly')) {
            $plugin_url = admin_url('admin.php?page=mls-on-the-fly-settings#chatbot');
            ?>
            <a href="<?php echo esc_url($plugin_url); ?>" class="support-float-icon" title="Get Support">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                </svg>
            </a>
            <?php
        }
    }
} 