<?php
/**
 * Created by Nabeel
 * Date: 2016-01-22
 * Time: 2:38 AM
 *
 * @package Codeable_Review_AutoPost
 */

use Codeable_Review_AutoPost\Component;
use Codeable_Review_AutoPost\Plugin;

if ( ! function_exists( 'codeable_review_auto_post' ) ):
	/**
	 * Get plugin instance
	 *
	 * @return Plugin
	 */
	function codeable_review_auto_post() {
		return Plugin::get_instance();
	}
endif;

if ( ! function_exists( 'crap_component' ) ):
	/**
	 * Get plugin component instance
	 *
	 * @param string $component_name
	 *
	 * @return Component|null
	 */
	function crap_component( $component_name ) {
		if ( isset( codeable_review_auto_post()->$component_name ) ) {
			return codeable_review_auto_post()->$component_name;
		}

		return null;
	}
endif;

if ( ! function_exists( 'crap_view' ) ):
	/**
	 * Load view
	 *
	 * @param string  $view_name
	 * @param array   $args
	 * @param boolean $return
	 *
	 * @return void
	 */
	function crap_view( $view_name, $args = null, $return = false ) {
		if ( $return ) {
			// start buffer
			ob_start();
		}

		codeable_review_auto_post()->load_view( $view_name, $args );

		if ( $return ) {
			// get buffer flush
			return ob_get_clean();
		}
	}
endif;

if ( ! function_exists( 'crap_version' ) ):
	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	function crap_version() {
		return codeable_review_auto_post()->version;
	}
endif;