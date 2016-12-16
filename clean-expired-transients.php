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
 * Version:     1.1
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
	}

	/**
	 * Add action links to plugins page.
	 *
	 * @since 1.1
	 * @deprecated 1.2
	 * @access public
	 *
	 * @param array  $links       Plugin's action links.
	 * @param string $plugin_file Path to the plugin file.
	 * @return array $links Plugin's action links.
	 */
	public function action_links( $links, $plugin_file ) {
		_deprecated_function( __METHOD__, '1.2' );

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