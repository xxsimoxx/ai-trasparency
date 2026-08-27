<?php

defined( 'ABSPATH' ) || exit;

/**
 * Handles AI Transparency plugin settings.
 */
class AI_Settings {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name = 'ai_transparency_settings';

	/**
	 * Current settings.
	 *
	 * @var array
	 */
	private $settings = [];

	/**
	 * Current settings version.
	 *
	 * @var int
	 */
	private $settings_version = 1;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->settings = wp_parse_args(
			get_option(
				$this->option_name,
				[]
			),
			[
				'version'                       => $this->settings_version,
				'process_content'               => false,
				'match_content_images_by_url'   => false,
				'process_output'                => false,
			]
		);

		$this->settings = $this->migrate_settings(
			$this->settings
		);
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {

		/*
		 * Settings page.
		 */
		add_action(
			'admin_menu',
			[ $this, 'add_settings_page' ]
		);

		/*
		 * Settings API.
		 */
		add_action(
			'admin_init',
			[ $this, 'register_settings' ]
		);

		/*
		 * Frontend processing filters.
		 *
		 * Priority 5 deliberately allows themes and plugins
		 * to override these values using the default priority 10.
		 */
		add_filter(
			'ai_transparency_process_content',
			[ $this, 'filter_process_content' ],
			5
		);

		add_filter(
			'ai_transparency_match_content_images_by_url',
			[ $this, 'filter_match_content_images_by_url' ],
			5
		);

		add_filter(
			'ai_transparency_process_output',
			[ $this, 'filter_process_output' ],
			5
		);

		/*
		 * Add settings link to plugin action links.
		 */
		add_filter(
			'plugin_action_links',
			[ $this, 'add_settings_link' ],
			10,
			2
		);
	}

	/**
	 * Add settings link to plugin action links.
	 *
	 * @return void
	 */
	public function add_settings_link( $links, $file ) {

		if ( $file !== plugin_basename( AI_TRANSPARENCY_FILE ) || ! current_user_can( 'manage_options' ) ) {
			return $links;
		}

		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'tools.php?page=ai-transparency' ),
			esc_html__( 'Settings', 'ai-transparency' )
		);
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Add the settings page under Tools.
	 *
	 * @return void
	 */
	public function add_settings_page() {

		add_management_page(
			__( 'AI Transparency', 'ai-transparency' ),
			__( 'AI Transparency', 'ai-transparency' ),
			'manage_options',
			'ai-transparency',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_settings() {

		register_setting(
			'ai_transparency_settings',
			$this->option_name,
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
				'default'           => [
					'version'                       => $this->settings_version,
					'process_content'               => false,
					'match_content_images_by_url'   => false,
					'process_output'                => false,
				],
			]
		);

		add_settings_section(
			'ai_transparency_content_section',
			__( 'Content scanning', 'ai-transparency' ),
			[ $this, 'render_section_description' ],
			'ai-transparency'
		);

		add_settings_field(
			'process_content',
			__( 'Process post content', 'ai-transparency' ),
			[ $this, 'render_checkbox' ],
			'ai-transparency',
			'ai_transparency_content_section',
			[
				'option'      => 'process_content',
				'label'       => __(
					'Scan the content of posts and pages for AI images.',
					'ai-transparency'
				),
				'description' => __(
					'Enable this if some images inside your posts or pages are not being detected by AI Transparency. When enabled, the plugin scans the HTML content and looks for images that may need an AI disclosure.',
					'ai-transparency'
				),
			]
		);

		add_settings_field(
			'match_content_images_by_url',
			__( 'Match images by URL', 'ai-transparency' ),
			[ $this, 'render_checkbox' ],
			'ai-transparency',
			'ai_transparency_content_section',
			[
				'option'      => 'match_content_images_by_url',
				'label'       => __(
					'Try to identify images by their URL.',
					'ai-transparency'
				),
				'description' => __(
					'Enable this when your theme or another plugin creates image tags without the usual WordPress attachment ID. AI Transparency will try to find the corresponding Media Library item using the image URL. This is particularly useful for custom image tags and lazy-loaded images.',
					'ai-transparency'
				),
			]
		);

		add_settings_field(
			'process_output',
			__( 'Process the final HTML output', 'ai-transparency' ),
			[ $this, 'render_checkbox' ],
			'ai-transparency',
			'ai_transparency_content_section',
			[
				'option'      => 'process_output',
				'label'       => __(
					'Scan the complete page before it is sent to visitors.',
					'ai-transparency'
				),
				'description' => __(
					'Enable this only if AI Transparency cannot detect some images using the other methods. The plugin scans the complete HTML generated by WordPress, the theme and other plugins. This uses PHP output buffering and may require additional server resources.',
					'ai-transparency'
				),
			]
		);
	}

	/**
	 * Render settings section description.
	 *
	 * @return void
	 */
	public function render_section_description() {

		echo '<p>';
		echo esc_html__(
			'These options control how AI Transparency searches for images that may require an AI disclosure. All options are disabled by default.',
			'ai-transparency'
		);
		echo '</p>';

		echo '<p>';
		echo esc_html__(
			'You normally do not need to enable these options unless some AI-classified images are not being detected on your website. Start with the least invasive option and only enable the final HTML scan if necessary.',
			'ai-transparency'
		);
		echo '</p>';
	}

	/**
	 * Render a checkbox setting.
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_checkbox( $args ) {

		$option = $args['option'];

		$value = ! empty(
			$this->settings[ $option ]
		);

		printf(
			'<label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s> %4$s</label>',
			esc_attr( $this->option_name ),
			esc_attr( $option ),
			checked(
				$value,
				true,
				false
			),
			esc_html( $args['label'] )
		);

		if ( ! empty( $args['description'] ) ) {

			printf(
				'<p class="description">%s</p>',
				esc_html(
					$args['description']
				)
			);
		}
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1>
				<?php
				echo esc_html__(
					'AI Transparency',
					'ai-transparency'
				);
				?>
			</h1>

			<form method="post" action="options.php">

				<?php

				settings_fields(
					'ai_transparency_settings'
				);

				do_settings_sections(
					'ai-transparency'
				);

				submit_button();

				?>

			</form>
		</div>
		<?php
	}

	/**
	 * Sanitize settings.
	 *
	 * @param mixed $input Submitted settings.
	 * @return array
	 */
	public function sanitize_settings( $input ) {

		if ( ! is_array( $input ) ) {
			$input = [];
		}

		return [
			'version' => $this->settings_version,

			'process_content' => ! empty(
				$input['process_content']
			),

			'match_content_images_by_url' => ! empty(
				$input['match_content_images_by_url']
			),

			'process_output' => ! empty(
				$input['process_output']
			),
		];
	}

	/**
	 * Migrate settings from older versions.
	 *
	 * @param array $settings Settings.
	 * @return array
	 */
	private function migrate_settings( $settings ) {

		$version = isset( $settings['version'] )
			? absint( $settings['version'] )
			: 0;

		/*
		 * Version 0 represents settings created before
		 * the version field was introduced.
		 */
		if ( $version < 1 ) {

			$settings['version'] = 1;

			update_option(
				$this->option_name,
				$settings
			);
		}

		return $settings;
	}

	/**
	 * Filter post content processing.
	 *
	 * @param bool $value Current filter value.
	 * @return bool
	 */
	public function filter_process_content( $value ) {

		return ! empty(
			$this->settings['process_content']
		);
	}

	/**
	 * Filter URL matching.
	 *
	 * @param bool $value Current filter value.
	 * @return bool
	 */
	public function filter_match_content_images_by_url( $value ) {

		return ! empty(
			$this->settings['match_content_images_by_url']
		);
	}

	/**
	 * Filter final HTML output processing.
	 *
	 * @param bool $value Current filter value.
	 * @return bool
	 */
	public function filter_process_output( $value ) {

		return ! empty(
			$this->settings['process_output']
		);
	}
}
