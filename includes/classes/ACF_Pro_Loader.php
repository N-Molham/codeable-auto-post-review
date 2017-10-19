<?php namespace Codeable_Review_AutoPost;

/**
 * Embedding ACF Pro component
 *
 * @package Codeable_Review_AutoPost
 */
class ACF_Pro_Loader extends Component
{
	/**
	 * ACF Pro internal location
	 *
	 * @var string
	 */
	public static $path = 'includes/vendors/acf-pro/';

	/**
	 * JSON load/save path
	 *
	 * @var string
	 */
	public static $json = 'includes/acf-json/';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init()
	{
		parent::init();

		$acf_file_path = CRAP_DIR . self::$path . 'acf.php';
		if ( false === file_exists( $acf_file_path ) )
		{
			// file not found!
			return;
		}

		// customize ACF Pro URL and directory path
		add_filter( 'acf/settings/path', [ &$this, 'settings_path' ], 20 );
		add_filter( 'acf/settings/dir', [ &$this, 'settings_dir' ], 20 );

		// JSON files
		add_filter( 'acf/settings/save_json', [ &$this, 'save_json_path' ], 20 );
		add_filter( 'acf/settings/load_json', [ &$this, 'load_json_path' ], 20 );

		// hide from admin menu
		add_filter( 'acf/settings/show_admin', defined( 'WP_DEBUG' ) && WP_DEBUG ? '__return_true' : '__return_false', 20 );

		// load ACF Pro main file
		require_once $acf_file_path;
	}

	/**
	 * JSON files loading path
	 *
	 * @return string
	 */
	public static function load_json_path( $paths )
	{
		if ( !is_array( $paths ) )
		{
			// set array
			$paths = [];
		}

		// append new path
		$paths[] = CRAP_DIR . self::$json;

		return $paths;
	}

	/**
	 * JSON files saving path
	 *
	 * @return string
	 */
	public static function save_json_path()
	{
		// return
		return CRAP_DIR . self::$json;
	}

	/**
	 * Update ACF Pro path
	 *
	 * @return string
	 */
	public static function settings_path()
	{
		// return
		return CRAP_DIR . self::$path;
	}

	/**
	 * Update ACF Pro dir
	 *
	 * @return string
	 */
	public static function settings_dir()
	{
		// return
		return CRAP_URI . self::$path;
	}
}
