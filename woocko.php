<?php
/**
 * Plugin Name: Woocko - Mail importer for WooCommerce
 * Plugin URI: https://nemc.in/woocko
 * Description: An WooCommerce addon that will learn your WooCommerce how to read emails and attachments and to update products accordingly.
 * Version: 0.1.0
 * Author: Nemanja Cimbaljevic
 * Author URI: https://nemc.in
 * Requires at least: 4.4
 * Tested up to: 4.7
 *
 * Text Domain: woocko
 *
 * @package Woocko
 * @category Core
 * @author Nemanja Cimbaljevic
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

final class Woocko
{
    const VERSION = '0.1.0';
    const PLUGIN_FILE = __FILE__;
    const LOG_DIR = "woocko-logs";

    protected static $_instance = null;

    protected $query = null;
    protected $db = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function db()
    {
        return self::instance()->db;
    }

    public static function query()
    {
        return self::instance()->query;
    }

    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocko'), '0.1.0');
    }

    public function __construct()
    {
        global $wpdb;
        $this->query = new WP_Query();
        $this->db = $wpdb;

        $this->includes();
        $this->init_hooks();
    }

    private function is_request($type)
    {
        switch ($type) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined('DOING_AJAX');
            case 'cron' :
                return defined('DOING_CRON');
            case 'frontend' :
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }

    private function includes()
    {
        include_once('includes/class-woocko-autoloader.php');
        include_once('includes/class-woocko-install.php');

        if ($this->is_request('admin')) {
//            include_once('includes/admin/class-woocko-admin.php');
        }

        if ($this->is_request('frontend') || $this->is_request('cron')) {
//            include_once('includes/class-woocko-session-handler.php');
        }
    }

    private function init_hooks()
    {
        register_activation_hook(__FILE__, array('Woocko_Install', 'install'));
    }

    public function load_plugin_textdomain()
    {
        $locale = apply_filters('plugin_locale', get_locale(), 'woocommerce');

        load_textdomain('woocommerce', WP_LANG_DIR . '/woocommerce/woocommerce-' . $locale . '.mo');
        load_plugin_textdomain('woocommerce', false, plugin_basename(dirname(Woocko::PLUGIN_FILE)) . '/i18n/languages');
    }
}

Woocko::instance();