<?php namespace Codeable_AutoPost_Review;

/**
 * Base Component
 *
 * @package Codeable_AutoPost_Review
 */
class Component extends Singular {

	/**
	 * Plugin Main Component
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {

		// vars
		$this->plugin = Plugin::get_instance();
		
	}
}
