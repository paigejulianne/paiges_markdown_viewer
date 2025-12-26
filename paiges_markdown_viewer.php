<?php
/**
 * Plugin Name: Paige's Markdown Viewer
 * Description: A customizable WordPress block that renders Markdown content with proper styling.
 * Version: 1.0.0
 * Author: Paige Julianne Sullivan
 * Author URI: https://paigejulianne.com/
 * License: GPL-2.0-or-later
 * Text Domain: paiges_markdown_viewer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue thickbox on plugins page for View Details modal
 */
function paiges_markdown_viewer_admin_scripts($hook) {
    if ($hook === 'plugins.php') {
        add_thickbox();
    }
}
add_action('admin_enqueue_scripts', 'paiges_markdown_viewer_admin_scripts');

// Include Parsedown library
require_once plugin_dir_path(__FILE__) . 'lib/Parsedown.php';

/**
 * Register the block
 */
function paiges_markdown_viewer_register() {
    // Register editor script
    wp_register_script(
        'paiges_markdown_viewer-editor',
        plugins_url('src/index.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor'),
        '1.0.0'
    );

    // Register editor styles
    wp_register_style(
        'paiges_markdown_viewer-editor-style',
        plugins_url('src/editor.css', __FILE__),
        array(),
        '1.0.0'
    );

    // Register frontend styles
    wp_register_style(
        'paiges_markdown_viewer-style',
        plugins_url('src/style.css', __FILE__),
        array(),
        '1.0.0'
    );

    // Register the block
    register_block_type('paiges-markdown-viewer/markdown', array(
        'editor_script' => 'paiges_markdown_viewer-editor',
        'editor_style' => 'paiges_markdown_viewer-editor-style',
        'style' => 'paiges_markdown_viewer-style',
        'render_callback' => 'paiges_markdown_viewer_render',
        'attributes' => array(
            'sourceType' => array(
                'type' => 'string',
                'default' => 'direct'
            ),
            'markdownContent' => array(
                'type' => 'string',
                'default' => ''
            ),
            'markdownUrl' => array(
                'type' => 'string',
                'default' => ''
            ),
            'maxHeight' => array(
                'type' => 'number',
                'default' => 0
            )
        )
    ));
}
add_action('init', 'paiges_markdown_viewer_register');

/**
 * Render callback for the markdown block
 */
function paiges_markdown_viewer_render($attributes) {
    $markdown_content = '';
    $source_type = isset($attributes['sourceType']) ? $attributes['sourceType'] : 'direct';

    if ($source_type === 'url' && !empty($attributes['markdownUrl'])) {
        // Fetch markdown from URL
        $url = esc_url($attributes['markdownUrl']);

        // Handle GitHub URLs - convert to raw URL
        if (preg_match('#github\.com/([^/]+)/([^/]+)/blob/(.+)#', $url, $matches)) {
            $url = 'https://raw.githubusercontent.com/' . $matches[1] . '/' . $matches[2] . '/' . $matches[3];
        }

        // Use transient caching to avoid repeated requests
        $cache_key = 'paiges_markdown_viewer_' . md5($url);
        $markdown_content = get_transient($cache_key);

        if ($markdown_content === false) {
            $response = wp_remote_get($url, array(
                'timeout' => 15,
                'sslverify' => true
            ));

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $markdown_content = wp_remote_retrieve_body($response);
                // Cache for 5 minutes
                set_transient($cache_key, $markdown_content, 5 * MINUTE_IN_SECONDS);
            } else {
                $markdown_content = '**Error:** Unable to fetch markdown from the specified URL.';
            }
        }
    } else {
        // Use direct markdown content
        $markdown_content = isset($attributes['markdownContent']) ? $attributes['markdownContent'] : '';
    }

    if (empty($markdown_content)) {
        return '';
    }

    // Parse markdown to HTML
    $parsedown = new Parsedown();
    $parsedown->setSafeMode(true); // Enable safe mode to prevent XSS
    $html_content = $parsedown->text($markdown_content);

    // Escape shortcode-like syntax to prevent WordPress from processing them
    $html_content = preg_replace('/\[(\/?[a-zA-Z_][a-zA-Z0-9_]*)/', '&#91;$1', $html_content);

    // Handle max height setting
    $max_height = isset($attributes['maxHeight']) ? intval($attributes['maxHeight']) : 0;
    $inline_style = '';
    $extra_class = 'markdown-viewer-content';

    if ($max_height > 0) {
        $inline_style = sprintf('max-height: %dpx; overflow-y: auto;', $max_height);
        $extra_class .= ' has-max-height';
    }

    // Get block wrapper attributes
    $wrapper_attributes = get_block_wrapper_attributes(array(
        'class' => $extra_class,
        'style' => $inline_style
    ));

    return sprintf(
        '<div %s>%s</div>',
        $wrapper_attributes,
        $html_content
    );
}

/**
 * Shortcode handler for markdown content
 * Usage:
 *   [paiges_markdown]Your **markdown** here[/paiges_markdown]
 *   [paiges_markdown url="https://example.com/file.md"]
 *   [paiges_markdown url="https://github.com/user/repo/blob/main/README.md" max_height="400"]
 */
function paiges_markdown_viewer_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'url' => '',
        'max_height' => 0,
    ), $atts, 'paiges_markdown');

    $markdown_content = '';

    if (!empty($atts['url'])) {
        // Fetch markdown from URL
        $url = esc_url($atts['url']);

        // Handle GitHub URLs - convert to raw URL
        if (preg_match('#github\.com/([^/]+)/([^/]+)/blob/(.+)#', $url, $matches)) {
            $url = 'https://raw.githubusercontent.com/' . $matches[1] . '/' . $matches[2] . '/' . $matches[3];
        }

        // Use transient caching to avoid repeated requests
        $cache_key = 'paiges_markdown_viewer_' . md5($url);
        $markdown_content = get_transient($cache_key);

        if ($markdown_content === false) {
            $response = wp_remote_get($url, array(
                'timeout' => 15,
                'sslverify' => true
            ));

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $markdown_content = wp_remote_retrieve_body($response);
                // Cache for 5 minutes
                set_transient($cache_key, $markdown_content, 5 * MINUTE_IN_SECONDS);
            } else {
                $markdown_content = '**Error:** Unable to fetch markdown from the specified URL.';
            }
        }
    } elseif (!empty($content)) {
        // Use content between shortcode tags
        $markdown_content = $content;
    }

    if (empty($markdown_content)) {
        return '';
    }

    // Parse markdown to HTML
    $parsedown = new Parsedown();
    $parsedown->setSafeMode(true);
    $html_content = $parsedown->text($markdown_content);

    // Escape shortcode-like syntax to prevent WordPress from processing them
    $html_content = preg_replace('/\[(\/?[a-zA-Z_][a-zA-Z0-9_]*)/', '&#91;$1', $html_content);

    // Handle max height setting
    $max_height = intval($atts['max_height']);
    $inline_style = '';
    $extra_class = 'markdown-viewer-content';

    if ($max_height > 0) {
        $inline_style = sprintf(' style="max-height: %dpx; overflow-y: auto;"', $max_height);
        $extra_class .= ' has-max-height';
    }

    // Enqueue styles when shortcode is used
    wp_enqueue_style(
        'paiges_markdown_viewer-style',
        plugins_url('src/style.css', __FILE__),
        array(),
        '1.0.0'
    );

    return sprintf(
        '<div class="%s"%s>%s</div>',
        esc_attr($extra_class),
        $inline_style,
        $html_content
    );
}
add_shortcode('paiges_markdown', 'paiges_markdown_viewer_shortcode');

/**
 * Enqueue frontend styles
 */
function paiges_markdown_viewer_enqueue_styles() {
    if (has_block('paiges-markdown-viewer/markdown')) {
        wp_enqueue_style(
            'paiges_markdown_viewer-style',
            plugins_url('src/style.css', __FILE__),
            array(),
            '1.0.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'paiges_markdown_viewer_enqueue_styles');
add_action('enqueue_block_assets', 'paiges_markdown_viewer_enqueue_styles');

/**
 * Add View Details link to plugin row meta (author line)
 */
function paiges_markdown_viewer_row_meta($links, $file) {
    if ($file === 'paiges_markdown_viewer/paiges_markdown_viewer.php') {
        $details_link = sprintf(
            '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
            esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=paiges_markdown_viewer&TB_iframe=true&width=600&height=550')),
            esc_attr__("More information about Paige's Markdown Viewer"),
            esc_attr__("Paige's Markdown Viewer"),
            __('View Details')
        );
        $links[] = $details_link;
        $links[] = '<a href="https://www.paypal.com/donate/?hosted_button_id=3X8QMH7RTRTGN" target="_blank" rel="noopener noreferrer">' . __('Donate') . '</a>';
    }
    return $links;
}
add_filter('plugin_row_meta', 'paiges_markdown_viewer_row_meta', 10, 2);

/**
 * Provide plugin information for the details popup
 */
function paiges_markdown_viewer_plugin_info($result, $action, $args) {
    if ($action !== 'plugin_information') {
        return $result;
    }

    if (!isset($args->slug) || $args->slug !== 'paiges_markdown_viewer') {
        return $result;
    }

    $plugin_info = new stdClass();
    $plugin_info->name = "Paige's Markdown Viewer";
    $plugin_info->slug = 'paiges_markdown_viewer';
    $plugin_info->version = '1.0.0';
    $plugin_info->author = '<a href="https://paigejulianne.com/">Paige Julianne Sullivan</a>';
    $plugin_info->homepage = 'https://paigejulianne.com/';
    $plugin_info->requires = '5.0';
    $plugin_info->tested = '6.4';
    $plugin_info->requires_php = '7.4';
    $plugin_info->downloaded = 0;
    $plugin_info->last_updated = date('Y-m-d');
    $plugin_info->sections = array(
        'description' => '
            <p>A customizable WordPress block that renders Markdown content with proper styling. Enter markdown directly in the editor or load it from a URL (including GitHub files).</p>
            <h4>Features</h4>
            <ul>
                <li>Gutenberg block for the WordPress editor</li>
                <li>Shortcode support for classic editor and widgets</li>
                <li>Load markdown from external URLs</li>
                <li>Automatic GitHub URL conversion to raw format</li>
                <li>Scrollable content with max-height option</li>
                <li>Clean, readable styling with dark mode support</li>
            </ul>
        ',
        'installation' => '
            <ol>
                <li>Upload the plugin folder to <code>/wp-content/plugins/</code></li>
                <li>Activate the plugin through the Plugins menu in WordPress</li>
                <li>Use the block editor to add "Paige\'s Markdown Viewer" or use the shortcode</li>
            </ol>
        ',
        'usage' => '
            <h4>Block Editor</h4>
            <p>Search for "Markdown" in the block inserter or find it in the Text category.</p>
            <h4>Shortcode</h4>
            <p>Use the <code>[paiges_markdown]</code> shortcode:</p>
            <pre>[paiges_markdown]
# Your Markdown Here
This is **bold** and *italic* text.
[/paiges_markdown]</pre>
            <p>Or load from a URL:</p>
            <pre>[paiges_markdown url="https://example.com/readme.md"]</pre>
            <pre>[paiges_markdown url="https://github.com/user/repo/blob/main/README.md" max_height="400"]</pre>
        ',
        'other_notes' => '
            <h4>Support Development</h4>
            <p>If you find this plugin useful, please consider <a href="https://www.paypal.com/donate/?hosted_button_id=3X8QMH7RTRTGN" target="_blank" rel="noopener noreferrer">making a donation</a> to support continued development.</p>
        ',
    );
    $plugin_info->donate_link = 'https://www.paypal.com/donate/?hosted_button_id=3X8QMH7RTRTGN';
    $plugin_info->banners = array();
    $plugin_info->icons = array();

    return $plugin_info;
}
add_filter('plugins_api', 'paiges_markdown_viewer_plugin_info', 20, 3);
