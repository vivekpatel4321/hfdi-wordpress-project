<?php
if (!defined('ABSPATH')) {
    exit;
}

$changelog_file = REALTYNA_MLS_ON_THE_FLY_DIR . 'CHANGELOG.md';
$changelog_content = '';

if (file_exists($changelog_file)) {
    $changelog_content = file_get_contents($changelog_file);
    
    // Convert markdown headers to HTML
    $changelog_content = preg_replace('/^##\s+(.+)$/m', '<h2>$1</h2>', $changelog_content);
    $changelog_content = preg_replace('/^###\s+(.+)$/m', '<h3>$1</h3>', $changelog_content);
    
    // Convert other markdown elements
    $changelog_content = preg_replace('/^\*\s+(.+)$/m', '<li>$1</li>', $changelog_content);
    $changelog_content = preg_replace('/`([^`]+)`/', '<code>$1</code>', $changelog_content);
    
    // Wrap lists in ul tags
    $changelog_content = preg_replace('/(<li>.*<\/li>\n?)+/', '<ul>$0</ul>', $changelog_content);
    
    // Convert remaining paragraphs
    $changelog_content = wpautop($changelog_content);
}
?>

<div class="wrap mls-on-the-fly-changelog">
    <h1><?php echo esc_html__('Changelog', 'mls-on-the-fly'); ?></h1>
    
    <div class="changelog-content">
        <?php if ($changelog_content): ?>
            <?php echo wp_kses_post($changelog_content); ?>
        <?php else: ?>
            <p><?php echo esc_html__('No changelog information available.', 'mls-on-the-fly'); ?></p>
        <?php endif; ?>
    </div>
</div> 