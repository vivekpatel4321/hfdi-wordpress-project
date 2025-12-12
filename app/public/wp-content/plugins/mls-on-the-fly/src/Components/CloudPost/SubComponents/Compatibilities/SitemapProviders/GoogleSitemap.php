<?php
namespace Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Compatibilities\SitemapProviders;


use DI\DependencyException;
use DI\NotFoundException;
use OutOfBoundsException;
use Realtyna\MlsOnTheFly\Boot\App;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Interfaces\IntegrationInterface;
use WP_Query;
use wpl_db;
use wpl_global;
use wpl_property;
use wpl_settings;
use GoogleSitemapGenerator;

class GoogleSitemap
{
    private string $last_modification_date;
    private IntegrationInterface $activeIntegration;
    private $sitemapObject;
    private $newVersion;

    public function __construct($sitemapObject)
    {
        $this->sitemapObject = $sitemapObject;
		$this->newVersion = method_exists($this->sitemapObject, 'addSitemap');

        try {
            $this->activeIntegration = App::get(IntegrationInterface::class);
        } catch (DependencyException|NotFoundException $e) {
        }
    }

    public function addIndexLinks($max_entries)
    {
        $post_types = $this->activeIntegration->customPostTypes;
        $last_modified_times = [];

        foreach ($post_types as $post_type) {
            $total_count = $this->getPostTypeCount($post_type);
            $last_modified_times[$post_type] = $this->last_modification_date;
            if ($total_count === 0) {
                continue;
            }

            $max_pages = ($total_count > $max_entries) ? (int)ceil($total_count / $max_entries) : 1;

            $date = $last_modified_times[$post_type];
            if($date == '0000-00-00 00:00:00'){
                $date = date('Y-m-d H:i:s');
                $date = date('Y-m-d H:i:s', strtotime($date . ' - 2 hours'));
            }
			$timestamp = strtotime($date);
            for ($page_counter = 0; $page_counter < $max_pages; $page_counter++) {
                $current_page = ($page_counter === 0) ? '' : ($page_counter + 1);
				if($this->newVersion) {
					$this->sitemapObject->addSitemap('cloud-' . $post_type . '-sitemap' . $current_page, null, $timestamp);
				} else {
					$this->sitemapObject->add_sitemap('cloud-' . $post_type . '-sitemap' . $current_page, null, $timestamp);
				}

            }
        }
    }

    public function getSitemapLinks($type, $max_entries, $current_page)
    {
        $type = str_replace('cloud-', '', $type);
        if (!in_array($type, $this->activeIntegration->customPostTypes)) {
            throw new OutOfBoundsException('Invalid sitemap page requested');
        }
        $steps = min(100, $max_entries);
        $offset = ($current_page > 1) ? (($current_page - 1) * $max_entries) : 0;
        $total = ($offset + $max_entries);
        $post_type_entries = $this->getPostTypeCount($type);

        if ($total > $post_type_entries) {
            $total = $post_type_entries;
        }

        if ($post_type_entries < $offset) {
            throw new OutOfBoundsException('Invalid sitemap page requested');
        }

        if ($post_type_entries === 0) {
            return;
        }

		if ($this->newVersion) {
			$sitemap = &GoogleSitemapGenerator::GetInstance();
		} else {
			$sitemap = &GoogleSitemapGenerator::get_instance();
		}

        while ($total > $offset) {
            $posts = $this->getPosts($type, $steps, $offset);
            $offset += $steps;

            if (empty($posts)) {
                continue;
            }
			$priority = 0.6;
			$changeFrequency = 'weekly';

            foreach ($posts as $post) {
                $url = $this->getUrl($post);
                if (empty($url)) {
                    continue;
                }
				$modified = max($post->post_modified_gmt, $post->post_date_gmt);
				if ($modified !== '0000-00-00 00:00:00') {
					$modified = strtotime($modified);
				} else {
					$modified = time();
				}

				if ($this->newVersion) {
					$sitemap->addUrl($url, $modified, $changeFrequency, $priority);
				} else {
					$sitemap->add_url($url, $modified, $changeFrequency, $priority);
				}
            }
        }
    }

    protected function getPostTypeCount($post_type)
    {
        $args = [
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        $query = new WP_Query($args);

        if (count($query->posts) > 0 && isset($query->posts[0])) {
            $lastPost = $query->posts[0];
            $this->last_modification_date = $lastPost->post_date;
        }

        return $query->found_posts;
    }

    protected function getPosts($type, $count, $offset)
    {
        $args = [
            'post_type' => $type,
            'posts_per_page' => $count,
            'offset' => $offset,
            'orderby' => 'modified',
            'order' => 'ASC',
        ];

        $query = new WP_Query($args);
        $posts = $query->posts;
        $post_ids = wp_list_pluck($posts, 'ID');

        update_meta_cache('post', $post_ids);

        return $posts;
    }

    protected function getUrl($post)
    {
        if ($this->activeIntegration->name == 'wpl') {
            if (isset($post->post_name) && $post->post_name != '') {
                $rf_property = new \wpl_rf_property();
                $rows = $rf_property->rf_after_mapping([$post]);
                $wplUrl = \wpl_property::get_property_link($rf_property->parse_post_meta($rows[0], true));
                return $wplUrl;
            }
			return '';
        }
		return get_permalink($post);
    }
}
