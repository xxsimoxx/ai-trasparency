<?php

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
final class AI_Transparency {

	/**
	 * Singleton instance.
	 *
	 * @var AI_Transparency|null
	 */
	private static $instance = null;

	/**
	 * Media component.
	 *
	 * @var AI_Media
	 */
	private $media;

	/**
	 * Frontend component.
	 *
	 * @var AI_Frontend
	 */
	private $frontend;

	/**
	 * Get plugin instance.
	 *
	 * @return AI_Transparency
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {

		$this->media = new AI_Media();

		$this->frontend = new AI_Frontend(
			$this->media
		);

		$this->media->register_hooks();
		$this->frontend->register_hooks();
	}
}
