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
	 * Plugin settings.
	 *
	 * @var AI_Settings
	 */
	private $settings;

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
	 * Register text domain.
	 *
	 * @return void
	 */
	public function text_domain() {

		load_plugin_textdomain(
			'ai-transparency',
			false,
			dirname( plugin_basename( AI_TRANSPARENCY_FILE ) ) . '/languages'
		);
	}

	/**
	 * Constructor.
	 */
	private function __construct() {

		$this->settings = new AI_Settings();
		$this->settings->register_hooks();

		$this->media = new AI_Media();

		$this->frontend = new AI_Frontend(
			$this->media
		);

		$this->media->register_hooks();
		$this->frontend->register_hooks();

		/*
		 * Add action to register text domain.
		 */
		add_action(
			'plugins_loaded',
			[ $this, 'text_domain' ]
		);
	}
}
