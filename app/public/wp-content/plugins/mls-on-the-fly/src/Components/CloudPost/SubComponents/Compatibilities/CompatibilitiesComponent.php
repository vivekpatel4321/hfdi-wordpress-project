<?php

namespace Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Compatibilities;

use Realtyna\Core\Abstracts\ComponentAbstract;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Compatibilities\SitemapProviders\AIOSeo;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Compatibilities\SitemapProviders\GoogleSitemap;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Compatibilities\SitemapProviders\WPSEO_Cloud_Post_Type_Sitemap_Provider;

class CompatibilitiesComponent extends ComponentAbstract
{
    public function register(): void
    {
        add_filter('wpseo_sitemaps_providers', [$this, 'yoastCompatibility'], 10, 1);
        if (class_exists('\AIOSEO\Plugin\AIOSEO')) {
            $AIOSeo = new AIOSeo();
            $AIOSeo->init();
        }
		add_action('sm_build_index', [$this, 'addGoogleSitemaps'], 10, 1);
		add_action('sm_build_content', [$this, 'addGoogleSitemapLinks'], 10, 1);

    }

    public function yoastCompatibility($external_providers): array
    {
        $external_providers[] = new WPSEO_Cloud_Post_Type_Sitemap_Provider();
        return $external_providers;
    }

	public function addGoogleSitemaps($object) {
		$sitemap = new GoogleSitemap($object);
		$sitemap->addIndexLinks(200);
	}

	public function addGoogleSitemapLinks($object) {
		$sitemap = new GoogleSitemap($object);
		$url = $_SERVER['REQUEST_URI'];
		$regex = '/cloud-(?<type>.*)-sitemap(?<page>\d*)/m';
		preg_match_all($regex, $url, $matches, PREG_SET_ORDER, 0);
		$type = $matches[0]['type'];
		$page = $matches[0]['page'];
		if(empty($page)) {
			$page = 1;
		}
		$sitemap->getSitemapLinks($type, 200, $page);
	}
}