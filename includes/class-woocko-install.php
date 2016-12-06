<?php
/**
 * Installation related functions and actions.
 *
 * @author   Nemanja Cimbaljevic
 * @category Admin
 * @package  WooCommerce/Classes
 * @version  0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Woocko_Install
{
    private $wpdb;

    public static function init()
    {
        add_filter('plugin_action_links_' . plugin_basename(Woocko::PLUGIN_FILE), array(__CLASS__, 'plugin_action_links'));
        add_filter('plugin_row_meta', array(__CLASS__, 'plugin_row_meta'), 10, 2);
        add_filter('cron_schedules', array(__CLASS__, 'cron_schedules'));
    }

    public static function plugin_action_links($links)
    {
        $action_links = array(
            'settings' => '<a href="' . admin_url('admin.php?page=woocko-settings') . '" title="' . esc_attr(__('Configure Woocko Settings', 'woocommerce')) . '">' . __('Settings', 'woocko') . '</a>',
        );

        return array_merge($action_links, $links);
    }

    public static function plugin_row_meta($links, $file)
    {
        if ($file == plugin_basename(Woocko::PLUGIN_FILE)) {
            $row_meta = array(
                'support' => '<a href="' . esc_url(apply_filters('woocko_support_url', '#')) . '" title="' . esc_attr(__('Visit Premium Customer Support Forum', 'woocko')) . '">' . __('Premium Support', 'woocko') . '</a>',
            );

            return array_merge($links, $row_meta);
        }

        return (array)$links;
    }

    public static function install()
    {
        do_action('woocko_before_install');

        // Ensure needed classes are loaded
//        include_once('admin/class-woocko-admin-notices.php');

        self::create_tables();
        self::create_roles();

        flush_rewrite_rules();

        do_action('woocko_after_install');
    }

    private static function create_tables()
    {
        $wpdb = Woocko::instance()->db();
        $wpdb->hide_errors();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta(self::get_schema());
    }

    public static function create_roles()
    {
        global $wp_roles;

        if (!class_exists('WP_Roles')) {
            return;
        }

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        // Customer role
        add_role('mail sender', __('Mail Sender', 'woocko'), array(
            'read' => true
        ));
    }

    private function cron_schedules()
    {

    }

    private static function get_schema()
    {
        $wpdb = Woocko::instance()->db();
        $collate = '';

        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }

        $tables = "
        CREATE TABLE {$wpdb->prefix}woocko_mails (
          id int(11) unsigned NOT NULL AUTO_INCREMENT,
          sender varchar(100) NOT NULL DEFAULT '',
          subject varchar(100) NOT NULL DEFAULT '',
          attachments text,
          status enum('pending','success','failure') NOT NULL DEFAULT 'pending',
          created_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          updated_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (id)
        ) $collate;
        
        
        CREATE TABLE wp_woocko_imports (
          id int(11) unsigned NOT NULL AUTO_INCREMENT,
          mail_id int(11) unsigned DEFAULT NULL,
          data text,
          status enum('pending','locked','imported','failed') NOT NULL DEFAULT 'pending',
          imported_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          error text NOT NULL,
          PRIMARY KEY (id)
        ) $collate;";

        return $tables;
    }
}

Woocko_Install::init();