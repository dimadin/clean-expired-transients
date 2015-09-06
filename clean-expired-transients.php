<?php

/**
 * The Clean Expired Transients Plugin
 *
 * Safest and simplest transients garbage collector.
 *
 * @package Clean_Expired_Transients
 * @subpackage Main
 */

/**
 * Plugin Name: Clean Expired Transients
 * Plugin URI:  http://blog.milandinic.com/wordpress/plugins/clean-expired-transients/
 * Description: Safest and simplest transients garbage collector.
 * Author:      Milan Dinić
 * Author URI:  http://blog.milandinic.com/
 * Version:     1.0
 * Text Domain: clean-expired-transients
 * Domain Path: /languages/
 * License:     GPL
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Clean_Expired_Transients' ) ) :
/**
 * Clean expired transients.
 * 
 * @since 1.0
 */
class Clean_Expired_Transients {
	/**
	 * Hook to daily cron action.
	 * 
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_scheduled_delete', array( $this, 'clean' ) );

		// Register plugins action links filter
		add_filter( 'plugin_action_links',               array( $this, 'action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'action_links' ), 10, 2 );
	}

	/**
	 * Add action links to plugins page.
	 *
	 * @since 1.1
	 * @access public
	 *
	 * @param array  $links       Existing plugin's action links.
	 * @param string $plugin_file Path to the plugin file.
	 * @return array $links New plugin's action links.
	 */
	public function action_links( $links, $plugin_file ) {
		// Set basename
		$basename = plugin_basename( __FILE__ );

		// Check if it is for this plugin
		if ( $basename != $plugin_file ) {
			return $links;
		}

		// Load translations
		load_plugin_textdomain( 'clean-expired-transients', false, dirname( $basename ) . '/languages' );

		// Add new links
		$links['donate']   = '<a href="http://blog.milandinic.com/donate/">' . __( 'Donate', 'clean-expired-transients' ) . '</a>';
		$links['wpdev']    = '<a href="http://blog.milandinic.com/wordpress/custom-development/">' . __( 'WordPress Developer', 'clean-expired-transients' ) . '</a>';
		$links['premium']  = '<strong><a href="https://shop.milandinic.com/downloads/clean-expired-transients-plus/">' . __( 'Premium Version', 'clean-expired-transients' ) . '</a></strong>';

		return $links;
	}

	/**
	 * Clean all expired transients from database older than minute.
	 * 
	 * @since 1.0
	 * @access public
	 */
	public static function clean() {
		global $wpdb;

		// Older than minute, just for case
		$older_than_time = time() - MINUTE_IN_SECONDS;

		// Clean single site transients
		$transients = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT REPLACE(option_name, '_transient_timeout_', '') AS transient_name
				FROM {$wpdb->options}
				WHERE option_name LIKE '\_transient\_timeout\__%%'
				AND option_value < %s
				",
				$older_than_time
			)
		);

		foreach ( $transients as $transient ) {
			get_transient( $transient );
		}

		// Clean network wide transients
		if ( is_multisite() ) {
			$transients = $wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT REPLACE(meta_key, '_site_transient_timeout_', '') AS transient_name
					FROM {$wpdb->sitemeta}
					WHERE meta_key LIKE '\_site\_transient\_timeout\__%%'
					AND meta_value < %s
					",
					$older_than_time
				)
			);
		} else {
			$transients = $wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT REPLACE(option_name, '_site_transient_timeout_', '') AS transient_name
					FROM {$wpdb->options}
					WHERE option_name LIKE '\_site\_transient\_timeout\__%%'
					AND option_value < %s
					",
					$older_than_time
				)
			);
		}

		foreach ( $transients as $transient ) {
			get_site_transient( $transient );
		}

		/**
		 * Fires after cleaning is finished.
		 *
		 * @since 1.1
		 */
		do_action( 'clean_expired_transients_cleaned' );
	}
}

/**
 * Initialize a plugin.
 *
 * Load class when all plugins are loaded
 * so that other plugins can overwrite it.
 *
 * @since 1.0
 *
 * @uses Clean_Expired_Transients To initialize plugin.
 */
function cet_initialize() {
	global $clean_expired_transients;
	$clean_expired_transients = new Clean_Expired_Transients;
}
add_action( 'plugins_loaded', 'cet_initialize' );

endif;