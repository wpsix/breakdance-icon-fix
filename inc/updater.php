<?php

if (!defined('ABSPATH')) {
    exit;
}

class BreakdanceIconFixUpdater {
    private $plugin_slug;
    private $plugin_file;
    private $version;
    private $update_url;
    private $cache_key;
    private $cache_allowed;

    /**
     * Initialize the updater
     */
    public function __construct($plugin_file, $version, $update_url) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->version = $version;
        $this->update_url = $update_url;
        $this->cache_key = 'woo_gallery_update_info';
        $this->cache_allowed = true;

        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);

        // Clear cache when user manually checks for updates
        add_action('admin_init', array($this, 'clear_cache_on_update_check'));

        // Clear cache after plugin update completes
        add_action('upgrader_process_complete', array($this, 'clear_cache_after_update'), 10, 2);

        // Clear stale cache if we already have the latest version
        $this->maybe_clear_stale_cache();
    }

    /**
     * Clear stale cache if current version matches or exceeds cached remote version
     */
    private function maybe_clear_stale_cache() {
        $cached_info = get_transient($this->cache_key);
        if ($cached_info && isset($cached_info->version)) {
            if (version_compare($this->version, $cached_info->version, '>=')) {
                delete_transient($this->cache_key);

                // Also remove from WordPress's update transient
                $update_plugins = get_site_transient('update_plugins');
                if ($update_plugins && isset($update_plugins->response[$this->plugin_slug])) {
                    unset($update_plugins->response[$this->plugin_slug]);
                    set_site_transient('update_plugins', $update_plugins);
                }
            }
        }
    }

    /**
     * Clear cache when user manually checks for updates
     */
    public function clear_cache_on_update_check() {
        if (isset($_GET['force-check']) && $_GET['force-check'] == '1') {
            delete_transient($this->cache_key);
        }
    }

    /**
     * Clear cache after plugin update completes
     */
    public function clear_cache_after_update($upgrader, $options) {
        if ($options['action'] !== 'update' || $options['type'] !== 'plugin') {
            return;
        }

        if (isset($options['plugins']) && is_array($options['plugins'])) {
            foreach ($options['plugins'] as $plugin) {
                if ($plugin === $this->plugin_slug) {
                    delete_transient($this->cache_key);

                    // Remove this plugin from WordPress's update transient
                    $update_plugins = get_site_transient('update_plugins');
                    if ($update_plugins && isset($update_plugins->response[$this->plugin_slug])) {
                        unset($update_plugins->response[$this->plugin_slug]);
                        set_site_transient('update_plugins', $update_plugins);
                    }
                    break;
                }
            }
        }
    }

    /**
     * Check for plugin updates
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Get remote version info
        $remote_info = $this->get_remote_info();

        if ($remote_info && version_compare($this->version, $remote_info->version, '<')) {
            // Update available
            $plugin_data = array(
                'slug' => dirname($this->plugin_slug),
                'plugin' => $this->plugin_slug,
                'new_version' => $remote_info->version,
                'url' => $remote_info->homepage,
                'package' => $remote_info->download_url,
                'tested' => $remote_info->tested,
                'requires' => $remote_info->requires,
                'requires_php' => $remote_info->requires_php,
            );

            $transient->response[$this->plugin_slug] = (object) $plugin_data;
        } else {
            // No update available, remove from response if present
            unset($transient->response[$this->plugin_slug]);
        }

        return $transient;
    }

    /**
     * Provide plugin information for the update screen
     */
    public function plugin_info($false, $action, $args) {
        if ($action !== 'plugin_information') {
            return $false;
        }

        if (!isset($args->slug) || $args->slug !== dirname($this->plugin_slug)) {
            return $false;
        }

        $remote_info = $this->get_remote_info();

        if (!$remote_info) {
            return $false;
        }

        $plugin_info = array(
            'name' => $remote_info->name,
            'slug' => dirname($this->plugin_slug),
            'version' => $remote_info->version,
            'author' => $remote_info->author,
            'author_profile' => $remote_info->author_profile,
            'homepage' => $remote_info->homepage,
            'requires' => $remote_info->requires,
            'tested' => $remote_info->tested,
            'requires_php' => $remote_info->requires_php,
            'download_link' => $remote_info->download_url,
            'sections' => array(
                'description' => $remote_info->sections->description,
                'changelog' => $remote_info->sections->changelog,
            ),
            'banners' => array(
                'high' => isset($remote_info->banners->high) ? $remote_info->banners->high : '',
                'low' => isset($remote_info->banners->low) ? $remote_info->banners->low : '',
            ),
        );

        return (object) $plugin_info;
    }

    /**
     * Get remote plugin information from update server
     */
    private function get_remote_info() {
        // Check cache first
        if ($this->cache_allowed) {
            $cached_info = get_transient($this->cache_key);
            if ($cached_info !== false) {
                return $cached_info;
            }
        }

        // Make request to update server
        $response = wp_remote_get(
            $this->update_url,
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept' => 'application/json',
                ),
            )
        );

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if (empty($data)) {
            return false;
        }

        // Cache the result for 12 hours
        if ($this->cache_allowed) {
            set_transient($this->cache_key, $data, 12 * HOUR_IN_SECONDS);
        }

        return $data;
    }
}
