<?php

namespace Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Targets;


use Realtyna\Core\Utilities\SettingsField;
use Realtyna\MlsOnTheFly\Boot\App;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Interfaces\IntegrationInterface;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Mapping\Mapping;
use Realtyna\MlsOnTheFly\Settings\Settings;

/**
 * Integration class for Houzez.
 *
 * This class provides integration between the MLS On The Fly<sup>&reg;</sup> and Houzez, a WordPress theme
 * that allows users to create and manage property listings.
 *
 * The integration features include:
 *
 * - Setting the default post author for newly created posts.
 * - Setting the default contact ID for newly created property listings.
 * - Uploading the Cloud Post IDs to ensure synchronization with Houzez.
 *
 * @autor Chandler.p
 *         chandler.p@realtyna.com
 */
class RealtynaIntegration implements IntegrationInterface
{
    /**
     * An array of meta keys that are allowed to be saved.
     *
     * @var array
     */
    public array $metaWhiteList = [];
    public string $name = 'realtyna';
    public array $customPostTypes = [
        'mof_property',
    ];
    public array $customTaxonomies = [

    ];

    /**
     * Constructor.
     *
     * This method initializes the integration class by setting the allowed meta keys.
     * @throws \ReflectionException
     */
    public function __construct()
    {
        //Load this integration custom taxonomies array
         // TODO I had to use init hook to manage when does mapping files will be read
        add_action('init', function () {
            /** @var Mapping $mapping */
            $mapping = App::get(Mapping::class);
            $mappingFile = $mapping->mappingConfig->getQueryMapping();
            $this->customTaxonomies = array_keys($mappingFile['taxonomies']);
        }, 99999);

        return self::class;
    }


    /**
     * Checks if a meta key is allowed to be saved.
     *
     * This method checks if a meta key is allowed to be saved based on the white list of allowed meta keys. If the meta key is in the white list, it is allowed to be saved. If the meta key is not in the white list and it contains the string 'property_', it is not allowed to be saved.
     *
     * @param bool|null $check Whether to allow the meta key to be saved or not.
     * @param string $metaKey The meta key to be checked.
     *
     * @return bool|null Whether to allow the meta key to be saved or not.
     */
    public function checkMetaToSave($check, $metaKey): bool|null
    {
        if (in_array($metaKey, $this->metaWhiteList)) {
            return true;
        }

        return $check;
    }

    /**
     * Get the mapping directory.
     *
     * This function returns the mapping directory.
     *
     * @return string The mapping directory.
     */
    public function getMappingDir(): string
    {
        return '';
    }

}
