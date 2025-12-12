<?php

namespace Realtyna\MlsOnTheFly\Components\CloudPost\AdminPages;

use Realtyna\Core\Abstracts\AdminPageAbstract;

class ChangelogAdminPage extends AdminPageAbstract
{
    public function register(): void
    {
        parent::register();
    }

    protected function getPageTitle(): string
    {
        return 'Changelog';
    }

    protected function getMenuTitle(): string
    {
        return 'Changelog';
    }

    protected function getCapability(): string
    {
        return 'manage_options';
    }

    protected function getMenuSlug(): string
    {
        return 'mls-on-the-fly-changelog';
    }

    protected function getPageTemplate(): string
    {
        return REALTYNA_MLS_ON_THE_FLY_DIR . 'templates/admin/changelog.php';
    }

    protected function isSubmenu(): bool
    {
        return true;
    }

    protected function getParentSlug(): string
    {
        return 'mls-on-the-fly';
    }

    protected function getPosition(): int
    {
        return 100; // Place it at the end of the menu
    }

    public function enqueueGlobalAssets(): void
    {

    }

    public function enqueuePageAssets(): void
    {
        wp_register_style(
            'mls-on-the-fly-changelog',
            REALTYNA_MLS_ON_THE_FLY_URL . 'assets/css/mls-on-the-fly-changelog.css',
            array(),
            REALTYNA_MLS_ON_THE_FLY_VERSION
        );
        wp_enqueue_style('mls-on-the-fly-changelog');
    }
} 