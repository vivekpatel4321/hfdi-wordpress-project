<?php

namespace Realtyna\MlsOnTheFly\Components\CloudPost;


use Realtyna\Core\Abstracts\ComponentAbstract;
use Realtyna\MlsOnTheFly\Boot\App;
use Realtyna\MlsOnTheFly\Components\CloudPost\AdminPages\GlobalFiltersAdminPage;
use Realtyna\MlsOnTheFly\Components\CloudPost\AdminPages\MappingEditorAdminPage;
use Realtyna\MlsOnTheFly\Components\CloudPost\AdminPages\PropertiesNotesAdminPage;
use Realtyna\MlsOnTheFly\Components\CloudPost\AdminPages\SettingAdminPage;
use Realtyna\MlsOnTheFly\Components\CloudPost\AdminPages\TermsAdminPage;
use Realtyna\MlsOnTheFly\Components\CloudPost\AdminPages\ChangelogAdminPage;
use Realtyna\MlsOnTheFly\Components\CloudPost\APIEndpoints\V1\MappingEditor\DeleteMappingField;
use Realtyna\MlsOnTheFly\Components\CloudPost\APIEndpoints\V1\MappingEditor\GetMappingField;
use Realtyna\MlsOnTheFly\Components\CloudPost\APIEndpoints\V1\MappingEditor\DeleteQueryMappingField;
use Realtyna\MlsOnTheFly\Components\CloudPost\APIEndpoints\V1\MappingEditor\GetQueryMappingField;
use Realtyna\MlsOnTheFly\Components\CloudPost\APIEndpoints\V1\MappingEditor\ExportMapping;
use Realtyna\MlsOnTheFly\Components\CloudPost\APIEndpoints\V1\MappingEditor\ImportMapping;
use Realtyna\MlsOnTheFly\Components\CloudPost\APIEndpoints\V1\MappingEditor\ResetMapping;
use Realtyna\MlsOnTheFly\Components\CloudPost\APIEndpoints\V1\MappingEditor\UpdateQueryMappingField;
use Realtyna\MlsOnTheFly\Components\CloudPost\APIEndpoints\V1\MappingEditor\UpdateMappingField;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\CacheManager\CacheManagerComponent;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Compatibilities\CompatibilitiesComponent;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\IntegrationComponent;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Interfaces\IntegrationInterface;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\PostInjection\PostInjectionComponent;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\RFClient\RFClientComponent;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\RFClient\SDK\RF\RF;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\TermImporter\TermImporterComponent;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\AdminBar\AddAdminBar;
use Realtyna\MlsOnTheFly\Settings\Settings;

class CloudPostComponent extends ComponentAbstract
{
    private $last_run_option_name = 'mls_on_the_fly_last_data_track_time'; // Option name for last execution time

    public function register(): void
    {
    }

    /**
     * @throws \Exception
     */
    public function subComponents(): void
    {
        $this->addSubComponent(RFClientComponent::class);
        $this->addSubComponent(IntegrationComponent::class);
        if (App::has(RF::class)) {
            $this->addSubComponent(PostInjectionComponent::class);
            $this->addSubComponent(TermImporterComponent::class);
            $this->addSubComponent(CompatibilitiesComponent::class);
            $this->addSubComponent(AddAdminBar::class);
            $this->addSubComponent(CacheManagerComponent::class);
        }

        // Track integration data if it hasn't been tracked in the last 24 hours
        $this->track_integration_data_if_needed();
    }

    public function adminPages(): void
    {
        $this->addAdminPage(SettingAdminPage::class);
        $this->addAdminPage(TermsAdminPage::class);
        $this->addAdminPage(MappingEditorAdminPage::class);
        $this->addAdminPage(GlobalFiltersAdminPage::class);
        if (defined('MLS_ON_THE_FLY_WITH_BETA_FEATURES')){
            $this->addAdminPage(PropertiesNotesAdminPage::class);
        }
        $this->addAdminPage(ChangelogAdminPage::class);
    }

    public function restApiEndpoints(): void
    {
        $this->addRestApiEndpoint(GetMappingField::class, App::class);
        $this->addRestApiEndpoint(UpdateMappingField::class, App::class);
        $this->addRestApiEndpoint(DeleteMappingField::class, App::class);
	    $this->addRestApiEndpoint(GetQueryMappingField::class, App::class);
	    $this->addRestApiEndpoint(UpdateQueryMappingField::class, App::class);
        $this->addRestApiEndpoint(DeleteQueryMappingField::class, App::class);
        $this->addRestApiEndpoint(ExportMapping::class, App::class);
        $this->addRestApiEndpoint(ImportMapping::class, App::class);
        $this->addRestApiEndpoint(ResetMapping::class, App::class);
    }

    /**
     * Check if data tracking is needed and track integration data using the data tracker API.
     */
    private function track_integration_data_if_needed(): void
    {
        // Use UTC timestamp for consistent comparison
        $current_time = time();
        $last_run_time = get_option($this->last_run_option_name, 0);
        
        // Check if 24 hours have passed since the last run
        if (($current_time - $last_run_time) > DAY_IN_SECONDS) {
            // Try to acquire a lock
            $lock_key = 'mls_on_the_fly_tracking_lock';
            $lock_timeout = 300; // 5 minutes timeout
            
            // Check if we can acquire the lock
            if (get_transient($lock_key) === false) {
                // Set the lock
                set_transient($lock_key, true, $lock_timeout);
                
                try {
                    $this->track_integration_data();
                    update_option($this->last_run_option_name, $current_time);
                } finally {
                    // Always release the lock
                    delete_transient($lock_key);
                }
            }
        }
    }

    /**
     * Track integration data using the data tracker API
     */
    private function track_integration_data(): void
    {
        $data = $this->prepare_integration_data();
        $this->send_data_to_tracker($data);
    }

    /**
     * Prepare the data required for the integration tracker
     *
     * @return array
     */
    private function prepare_integration_data(): array
    {
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }
        
        // Retrieve credentials from settings
        $clientId = Settings::get_setting('client_id', false);
        $clientSecret = Settings::get_setting('client_secret', false);
        $apiKey = Settings::get_setting('api_key', false);

        // Get active integration status
        $activeIntegration = IntegrationComponent::getStaticIntegrationName();

        // Website URL and admin email
        $websiteURL = get_site_url();
        $adminEmail = get_option('admin_email');

        // Retrieve user action logs from the options table
        $userActionLogs = get_option('realtyna_otf_user_action_logs', []);

        $wpVersion = get_bloginfo('version');
        $phpVersion = phpversion();
        $mofVersion = REALTYNA_MLS_ON_THE_FLY_VERSION;
        $activeTheme = wp_get_theme()->get('Name');
        $activeThemeVersion = wp_get_theme()->get('Version');
        $activePlugins = get_option('active_plugins'); 
        $isMultisite = is_multisite();
        $isNetworkActive = is_plugin_active_for_network('mls-on-the-fly/mls-on-the-fly.php');

        $accessToken = get_transient('realtyna_mls_on_the_fly_rf_access_token');
        $token = $accessToken ?? '';

        // Prepare data to send, including the user action logs
        return [
            'plugin_id' => 1, // Replace with your actual plugin ID
            'token'     => $token,
            'data_type' => 'integration_status',
            'data_value' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'api_key' => $apiKey,
                'integration_type' => '',
                'active_integration' => $activeIntegration,
                'website_url' => $websiteURL,
                'admin_email' => $adminEmail,
                'wp_version' => $wpVersion,
                'php_version' => $phpVersion,
                'mof_version' => $mofVersion,
                'active_theme' => $activeTheme,
                'active_theme_version' => $activeThemeVersion,
                'active_plugins' => $activePlugins,
                'is_multisite' => $isMultisite,
                'is_network_active' => $isNetworkActive,
                'user_action_logs' => $userActionLogs // Add the logs here
            ]
        ];
    }

    /**
     * Send data to the data tracker API
     *
     * @param array $data
     */
    private function send_data_to_tracker(array $data): void
    {
        $api_url = 'https://update.realtyna.com/wordpress/wp-json/realtyna/v1/track-data';
        $response = wp_remote_post($api_url, [
            'body' => json_encode($data),
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        if (is_wp_error($response)) {
            // Log error or handle it as needed
            error_log('Failed to send data to tracker: ' . $response->get_error_message());
        } else {
            // Handle successful response if needed
            $response_body = wp_remote_retrieve_body($response);
            error_log('Data sent to tracker: ' . $response_body);

            // Clear the user action logs after successfully sending
            update_option('realtyna_otf_user_action_logs', ''); // Clear the logs after sending
        }
    }

}