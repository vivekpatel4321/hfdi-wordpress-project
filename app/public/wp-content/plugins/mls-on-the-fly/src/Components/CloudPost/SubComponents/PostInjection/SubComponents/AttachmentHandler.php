<?php

namespace Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\PostInjection\SubComponents;
use Realtyna\MlsOnTheFly\Components\CloudPost\SubComponents\Integration\Interfaces\IntegrationInterface;

class AttachmentHandler{

    public function __construct()
    {
        add_filter('get_attached_file', [$this, 'filterGetAttachedFile'], 10, 2);
        add_filter('wp_get_attachment_url', [$this, 'filterGetAttachmentUrl'], 10, 2);
        add_filter('wp_get_attachment_image_src', [$this, 'filterGetAttachmentImageSrc'], 10, 3);
        add_filter('image_downsize', [$this, 'filterImageDownsize'], 10, 3);
    }

    public function filterGetAttachedFile($file, $attachment_id)
    {
        if ($attachment_id < 0) {
            $attachment = get_post($attachment_id);

            return $attachment->media_url ?? $file;
        }
        return $file;
    }

    public function filterGetAttachmentUrl($url, $attachment_id)
    {
        if ($attachment_id < 0) {
            $attachment = get_post($attachment_id);
            return $attachment->media_url;
        }
        return $url;
    }

    /**
     * Filter image_downsize to handle cloud post attachments before WordPress returns false
     * This filter runs before wp_get_attachment_image_src and can intercept the request early
     */
    public function filterImageDownsize($downsize, $attachment_id, $size)
    {
        // Only handle cloud post attachments (negative IDs)
        if ($attachment_id < 0) {
            $attachment = $this->getAttachmentFromCache($attachment_id);
            
            if ($attachment && isset($attachment->media_url) && !empty($attachment->media_url)) {
                // Get dimensions based on size
                $dimensions = $this->getImageDimensions($size);
                
                // Return the image array with URL and dimensions
                return [
                    $attachment->media_url, // URL
                    $dimensions['width'],   // Width
                    $dimensions['height'],  // Height
                    $dimensions['crop']     // Crop
                ];
            }
        }
        
        return $downsize;
    }

    // Function to filter the image source of an attachment based on specific conditions.
    public function filterGetAttachmentImageSrc($image, $attachment_id, $size): array|bool
    {
        // Check if the attachment ID is invalid (less than 0).
        if ($attachment_id < 0) {
            // If the $image variable is a boolean (false), try to get the attachment from cache
            // This happens when WordPress can't find the attachment in the database
            // but it might be a cloud post attachment stored in cache
            if (is_bool($image) && $image === false) {
                $attachment = $this->getAttachmentFromCache($attachment_id);
                
                // If attachment exists in cache and has media_url, build the image array
                if ($attachment && isset($attachment->media_url) && !empty($attachment->media_url)) {
                    $dimensions = $this->getImageDimensions($size);
                    
                    $image = [
                        0 => $attachment->media_url, // URL
                        1 => $dimensions['width'],    // Width
                        2 => $dimensions['height'],   // Height
                        3 => $dimensions['crop']     // Crop
                    ];
                } else {
                    // Attachment doesn't exist or has no media_url, return false
                    return false;
                }
            } else if (is_array($image)) {
                // If $image is already an array, just update dimensions if needed
                $dimensions = $this->getImageDimensions($size);
                $image[1] = $dimensions['width'];
                $image[2] = $dimensions['height'];
                $image[3] = $dimensions['crop'];
            }
        }

        // Return the modified or original $image array.
        return $image;
    }

    /**
     * Get attachment from cache, trying multiple cache keys
     */
    private function getAttachmentFromCache($attachment_id)
    {
        // First try get_post which should check cache
        $attachment = get_post($attachment_id);
        
        if ($attachment && isset($attachment->media_url)) {
            return $attachment;
        }
        
        // If get_post didn't work, try direct cache access
        $attachment = wp_cache_get($attachment_id, 'posts');
        if ($attachment && isset($attachment->media_url)) {
            return $attachment;
        }
        
        // Try without cache group
        $attachment = wp_cache_get($attachment_id, '');
        if ($attachment && isset($attachment->media_url)) {
            return $attachment;
        }
        
        return null;
    }

    /**
     * Get image dimensions based on size parameter
     */
    private function getImageDimensions($size): array
    {
        $registeredImageSizes = wp_get_registered_image_subsizes();
        
        // Check if the requested size is 'post-thumbnail'.
        if ($size == 'post-thumbnail') {
            return [
                'width' => 90,
                'height' => 90,
                'crop' => true
            ];
        }
        
        // If $size is an array, extract width and height.
        if (is_array($size)) {
            return [
                'width' => $size[0] ?? 300,
                'height' => $size[1] ?? 300,
                'crop' => true
            ];
        }
        
        // Check if the size exists in the registered image sizes.
        if (isset($registeredImageSizes[$size])) {
            return [
                'width' => $registeredImageSizes[$size]['width'] ?? 300,
                'height' => $registeredImageSizes[$size]['height'] ?? 300,
                'crop' => $registeredImageSizes[$size]['crop'] ?? true
            ];
        }
        
        // Fallback to default dimensions if size is not registered.
        return [
            'width' => 300,
            'height' => 300,
            'crop' => true
        ];
    }


}