<?php
/**
 * Autoloader related functions and actions.
 *
 * @author   Nemanja Cimbaljevic
 * @category Admin
 * @package  WooCommerce/Classes
 * @version  0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Woocko_Autoloader
{
    private $include_path = '';

    public function __construct()
    {
        if (function_exists("__autoload")) {
            spl_autoload_register("__autoload");
        }

        spl_autoload_register(array($this, 'autoload'));

        $this->include_path = untrailingslashit(plugin_dir_path(Woocko::PLUGIN_FILE)) . '/includes/';
    }

    private function get_file_name_from_class($class)
    {
        return 'class-' . str_replace('_', '-', $class) . '.php';
    }

    private function load_file($path)
    {
        if ($path && is_readable($path)) {
            include_once($path);
            return true;
        }
        return false;
    }

    public function autoload( $class ) {
        $class = strtolower( $class );
        $file  = $this->get_file_name_from_class( $class );
        $path  = '';

        if ( strpos( $class, 'wc_addons_gateway_' ) === 0 ) {
            $path = $this->include_path . 'gateways/' . substr( str_replace( '_', '-', $class ), 18 ) . '/';
        } elseif ( strpos( $class, 'wc_gateway_' ) === 0 ) {
            $path = $this->include_path . 'gateways/' . substr( str_replace( '_', '-', $class ), 11 ) . '/';
        } elseif ( strpos( $class, 'wc_shipping_' ) === 0 ) {
            $path = $this->include_path . 'shipping/' . substr( str_replace( '_', '-', $class ), 12 ) . '/';
        } elseif ( strpos( $class, 'wc_shortcode_' ) === 0 ) {
            $path = $this->include_path . 'shortcodes/';
        } elseif ( strpos( $class, 'wc_meta_box' ) === 0 ) {
            $path = $this->include_path . 'admin/meta-boxes/';
        } elseif ( strpos( $class, 'wc_admin' ) === 0 ) {
            $path = $this->include_path . 'admin/';
        } elseif ( strpos( $class, 'wc_cli_' ) === 0 ) {
            $path = $this->include_path . 'cli/';
        } elseif ( strpos( $class, 'wc_payment_token_' ) === 0 ) {
            $path = $this->include_path . 'payment-tokens/';
        }

        if ( empty( $path ) || ( ! $this->load_file( $path . $file ) && strpos( $class, 'wc_' ) === 0 ) ) {
            $this->load_file( $this->include_path . $file );
        }
    }
}