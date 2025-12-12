<?php

namespace Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\PostInjection\SubComponents;
use Realtyna\MlsOnTheFly\Boot\Log;

class CloudPostIdHandler{

    /**
     * @var int|mixed
     */
    private int $cloudPostId;

    /**
     * Initializes the Cloud Post IDs used for synchronization between WordPress and RF.
     *
     * This method calculates and sets the initial Cloud Post ID based on the current state of the
     * WordPress and RF mappings. It ensures that the Cloud Post ID is correctly initialized to
     * maintain synchronization.
     *
     * @author Chandler.p chandler.p@realtyna.com
     */
    public function __construct()
    {
        // Get the last WordPress Post ID
        $wordPressLastPostID = $this->getWordPressLastPostID();
        Log::info('Getting last WordPress post ID: ' . $wordPressLastPostID);

        // Check if there is an RF mapping for the first and last RF Post IDs
        global $wpdb;
        $RFFirstPostID = $wpdb->get_row(
            "SELECT * FROM {$wpdb->prefix}realtyna_rf_mappings ORDER BY post_id ASC LIMIT 1"
        );
        $RFLastPostID = $wpdb->get_row(
            "SELECT * FROM {$wpdb->prefix}realtyna_rf_mappings ORDER BY post_id DESC LIMIT 1"
        );

        if (!$RFFirstPostID) {
            // If there is no RF post yet, set the first Cloud Post ID with a buffer of 1000
            // TODO: Consider making 1000 a dynamic value
            $cloudPostId = $wordPressLastPostID + 1000;
            Log::info('There is no RF post yet, setting first post ID to: ' . $cloudPostId);
        } else {
            // If there is at least one RF post, set the first Cloud Post ID to the next sequential ID
            $cloudPostId = $RFLastPostID->post_id + 1;
            Log::info('There is a RF post, setting first post ID to: ' . $cloudPostId);
        }

        // Check the distance between the first RF Post ID and the last WordPress Post ID
        if ($RFFirstPostID != null && $RFFirstPostID->post_id - $wordPressLastPostID < 200) {
            // If the distance is less than 200, log a warning
            $cloudPostId = $this->updateCloudPostIds();
            Log::warning('Distance between last WordPress post ID and first RF post ID is less than 100');
        }

        // Set the calculated Cloud Post ID for synchronization
        $this->cloudPostId = $cloudPostId;
    }

    /**
     * Get WordPress last post id
     *
     * @return mixed
     * @author Chandler.p chandler.p@realtyna.com
     */
    public function getWordPressLastPostID(): int
    {
        global $wpdb;
        $query = "SELECT ID FROM $wpdb->posts ORDER BY ID DESC LIMIT 0,1";
        $result = $wpdb->get_results($query);
        if (!empty($result)) {
            return $result[0]->ID;
        }

        return 0;
    }

    /**
     * Uploads Cloud Post IDs to ensure synchronization with RF mappings.
     *
     * This method takes the first RF Post ID as a reference and updates all RF mappings'
     * post IDs to ensure a consistent synchronization between WordPress and RF. It also
     * updates the corresponding postmeta records.
     *
     * @author Chandler.p chandler.p@realtyna.com
     *
     */
    public function updateCloudPostIds(): int
    {
        global $wpdb;

        // Table names with prefixes
        $rfMappingTable = $wpdb->prefix . 'realtyna_rf_mappings';
        $postmetaTable = $wpdb->prefix . 'postmeta';

        // Create a temporary mapping of old -> new IDs in one shot
        $tmpTable = $wpdb->prefix . 'tmp_rf_mappings_id_shift';

        // Drop temp table if exists (safety)
        $wpdb->query("DROP TEMPORARY TABLE IF EXISTS $tmpTable");

        // Build temp table with old and new ids (+1000)
        $createTemp = "CREATE TEMPORARY TABLE $tmpTable (
            id BIGINT(20) UNSIGNED NOT NULL,
            old_id BIGINT(20) UNSIGNED NOT NULL,
            new_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=Memory SELECT id, post_id AS old_id, post_id + 1000 AS new_id FROM $rfMappingTable";

        $wpdb->query($createTemp);

        // Update rf_mappings in bulk using the temp table (no collisions; function is injective)
        $updateMappings = "UPDATE $rfMappingTable rm
            INNER JOIN $tmpTable t ON rm.id = t.id
            SET rm.post_id = t.new_id";
        $wpdb->query($updateMappings);

        // Update postmeta in bulk using the same mapping
        $updatePostmeta = "UPDATE $postmetaTable pm
            INNER JOIN $tmpTable t ON pm.post_id = t.old_id
            SET pm.post_id = t.new_id";
        $wpdb->query($updatePostmeta);

        // Fire actions for each updated mapping (optional: iterate temp table ids only)
        $rows = $wpdb->get_results("SELECT old_id, new_id FROM $tmpTable");
        foreach ($rows as $row) {
            do_action('realtyna_mls_on_the_fly_update_cloud_post_ids', (int)$row->new_id, (int)$row->old_id);
        }

        // Compute next available id
        $lastId = (int)$wpdb->get_var("SELECT MAX(new_id) FROM $tmpTable");

        // Cleanup temp table
        $wpdb->query("DROP TEMPORARY TABLE IF EXISTS $tmpTable");

        return $lastId ? $lastId + 1 : 1;
    }

    public function getCloudPostId(): int
    {
        return $this->cloudPostId;
    }
}