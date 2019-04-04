<?php
/**
 * Created by Nabeel
 * Date: 2016-01-22
 * Time: 2:38 AM
 *
 * @package Codeable_AutoPost_Review
 */

use Codeable_AutoPost_Review\Backend;
use Codeable_AutoPost_Review\Codeable;
use Codeable_AutoPost_Review\Component;
use Codeable_AutoPost_Review\Plugin;
use Codeable_AutoPost_Review\Social_Media;

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

if ( ! function_exists( 'capr_codeable' ) ):
	/**
	 * @return Codeable
	 */
	function capr_codeable() {

		return codeable_review_auto_post()->codeable;
	}
endif;

if ( ! function_exists( 'capr_backend' ) ):
	/**
	 * @return Backend
	 */
	function capr_backend() {

		return codeable_review_auto_post()->backend;
	}
endif;

if ( ! function_exists( 'capr_social_media' ) ):
	/**
	 * @return Social_Media
	 */
	function capr_social_media() {

		return codeable_review_auto_post()->social_media;
	}
endif;

if ( ! function_exists( 'capr_component' ) ):
	/**
	 * Get plugin component instance
	 *
	 * @param string $component_name
	 *
	 * @return Component|null
	 */
	function capr_component( $component_name ) {

		if ( isset( codeable_review_auto_post()->$component_name ) ) {
			return codeable_review_auto_post()->$component_name;
		}

		return null;
	}
endif;

if ( ! function_exists( 'capr_view' ) ):
	/**
	 * Load view
	 *
	 * @param string  $view_name
	 * @param array   $args
	 * @param boolean $return
	 *
	 * @return void
	 */
	function capr_view( $view_name, $args = null, $return = false ) {

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

if ( ! function_exists( 'capr_version' ) ):
	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	function capr_version() {

		return codeable_review_auto_post()->version;
	}
endif;