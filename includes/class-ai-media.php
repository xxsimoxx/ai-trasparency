<?php

defined( 'ABSPATH' ) || exit;

/**
 * Handles AI metadata for media attachments.
 */
class AI_Media {

	/**
	 * AI content metadata key.
	 */
	private const META_KEY = '_ai_transparency_type';

	/**
	 * AI disclosure metadata key.
	 */
	private const DISCLOSURE_META_KEY = '_ai_transparency_disclosure';

	/**
	 * AI generated content.
	 */
	public const TYPE_GENERATED = 'generated';

	/**
	 * AI manipulated content.
	 */
	public const TYPE_MANIPULATED = 'manipulated';

	/**
	 * AI generated and manipulated content.
	 */
	public const TYPE_GENERATED_AND_MANIPULATED = 'generated-and-manipulated';

	/**
	 * Automatic disclosure.
	 */
	public const DISCLOSURE_AUTOMATIC = 'automatic';

	/**
	 * Disclosure required.
	 */
	public const DISCLOSURE_REQUIRED = 'required';

	/**
	 * Disclosure disabled.
	 */
	public const DISCLOSURE_DISABLED = 'disabled';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {

		add_filter(
			'attachment_fields_to_edit',
			[ $this, 'add_attachment_fields' ],
			10,
			2
		);

		add_filter(
			'attachment_fields_to_save',
			[ $this, 'save_attachment_fields' ],
			10,
			2
		);
	}

	/**
	 * Get AI type.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string
	 */
	public function get_type( $attachment_id ) {

		$type = get_post_meta(
			$attachment_id,
			self::META_KEY,
			true
		);

		if ( ! $this->is_valid_type( $type ) ) {
			return '';
		}

		return $type;
	}

	/**
	 * Set AI type.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $type          AI type.
	 * @return bool
	 */
	public function set_type( $attachment_id, $type ) {

		$attachment_id = absint( $attachment_id );

		if ( ! $attachment_id ) {
			return false;
		}

		if ( '' === $type ) {
			return delete_post_meta(
				$attachment_id,
				self::META_KEY
			);
		}

		if ( ! $this->is_valid_type( $type ) ) {
			return false;
		}

		return (bool) update_post_meta(
			$attachment_id,
			self::META_KEY,
			$type
		);
	}

	/**
	 * Get disclosure setting.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string
	 */
	public function get_disclosure( $attachment_id ) {

		$disclosure = get_post_meta(
			$attachment_id,
			self::DISCLOSURE_META_KEY,
			true
		);

		if ( ! $this->is_valid_disclosure( $disclosure ) ) {
			return self::DISCLOSURE_AUTOMATIC;
		}

		return $disclosure;
	}

	/**
	 * Set disclosure setting.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $disclosure     Disclosure setting.
	 * @return bool
	 */
	public function set_disclosure( $attachment_id, $disclosure ) {

		$attachment_id = absint( $attachment_id );

		if ( ! $attachment_id ) {
			return false;
		}

		if ( ! $this->is_valid_disclosure( $disclosure ) ) {
			return false;
		}

		return (bool) update_post_meta(
			$attachment_id,
			self::DISCLOSURE_META_KEY,
			$disclosure
		);
	}

	/**
	 * Determine whether the image should be disclosed.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	public function should_disclose( $attachment_id ) {

		$disclosure = $this->get_disclosure(
			$attachment_id
		);

		if ( self::DISCLOSURE_REQUIRED === $disclosure ) {
			return true;
		}

		if ( self::DISCLOSURE_DISABLED === $disclosure ) {
			return false;
		}

		return $this->automatic_disclosure(
			$attachment_id
		);
	}

	/**
	 * Determine automatic disclosure.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	private function automatic_disclosure( $attachment_id ) {

		/**
		 * Filters whether an AI disclosure should be displayed
		 * when the disclosure setting is set to automatic.
		 *
		 * @param bool $disclose      Whether to display the disclosure.
		 * @param int  $attachment_id Attachment ID.
		 */
		return (bool) apply_filters(
			'ai_transparency_automatic_disclosure',
			false,
			$attachment_id
		);
	}

	/**
	 * Get human-readable label.
	 *
	 * @param string $type AI type.
	 * @return string
	 */
	public function get_label( $type ) {

		$labels = [
			self::TYPE_GENERATED => __(
				'Content generated with artificial intelligence',
				'ai-transparency'
			),

			self::TYPE_MANIPULATED => __(
				'Content manipulated with artificial intelligence',
				'ai-transparency'
			),

			self::TYPE_GENERATED_AND_MANIPULATED => __(
				'Content generated and manipulated with artificial intelligence',
				'ai-transparency'
			),
		];

		return isset( $labels[ $type ] )
			? $labels[ $type ]
			: '';
	}

	/**
	 * Add AI fields to attachment edit form.
	 *
	 * @param array   $form_fields Attachment fields.
	 * @param WP_Post $post        Attachment post.
	 * @return array
	 */
	public function add_attachment_fields(
		$form_fields,
		$post
	) {

		$type = $this->get_type(
			$post->ID
		);

		$disclosure = $this->get_disclosure(
			$post->ID
		);

		$form_fields['ai_transparency_type'] = [
			'label' => __(
				'AI content',
				'ai-transparency'
			),

			'input' => 'html',

			'html' => $this->get_type_field_html(
				$post->ID,
				$type
			),

			'helps' => __(
				'Indicates whether this image was generated or manipulated using AI.',
				'ai-transparency'
			),
		];

		$form_fields['ai_transparency_disclosure'] = [
			'label' => __(
				'AI disclosure',
				'ai-transparency'
			),

			'input' => 'html',

			'html' => $this->get_disclosure_field_html(
				$post->ID,
				$disclosure
			),

			'helps' => __(
				'Controls whether an AI disclosure is displayed for this image.',
				'ai-transparency'
			),
		];

		return $form_fields;
	}

	/**
	 * Generate AI type select.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $value         Current value.
	 * @return string
	 */
	private function get_type_field_html(
		$attachment_id,
		$value
	) {

		$options = [
			'' => __(
				'Not AI-generated or manipulated',
				'ai-transparency'
			),

			self::TYPE_GENERATED => __(
				'AI generated',
				'ai-transparency'
			),

			self::TYPE_MANIPULATED => __(
				'AI manipulated',
				'ai-transparency'
			),

			self::TYPE_GENERATED_AND_MANIPULATED => __(
				'AI generated and manipulated',
				'ai-transparency'
			),
		];

		$html = sprintf(
			'<select name="attachments[%1$d][ai_transparency_type]">',
			(int) $attachment_id
		);

		foreach ( $options as $key => $label ) {

			$html .= sprintf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $key ),
				selected(
					$value,
					$key,
					false
				),
				esc_html( $label )
			);
		}

		$html .= '</select>';

		return $html;
	}

	/**
	 * Generate disclosure select.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $value         Current value.
	 * @return string
	 */
	private function get_disclosure_field_html(
		$attachment_id,
		$value
	) {

		$options = [
			self::DISCLOSURE_AUTOMATIC => __(
				'Automatic',
				'ai-transparency'
			),

			self::DISCLOSURE_REQUIRED => __(
				'Required',
				'ai-transparency'
			),

			self::DISCLOSURE_DISABLED => __(
				'Disabled',
				'ai-transparency'
			),
		];

		$html = sprintf(
			'<select name="attachments[%1$d][ai_transparency_disclosure]">',
			(int) $attachment_id
		);

		foreach ( $options as $key => $label ) {

			$html .= sprintf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $key ),
				selected(
					$value,
					$key,
					false
				),
				esc_html( $label )
			);
		}

		$html .= '</select>';

		return $html;
	}

	/**
	 * Save attachment fields.
	 *
	 * @param array $post       Attachment data.
	 * @param array $attachment Attachment fields.
	 * @return array
	 */
	public function save_attachment_fields(
		$post,
		$attachment
	) {

		if ( isset( $attachment['ai_transparency_type'] ) ) {

			$type = sanitize_key(
				$attachment['ai_transparency_type']
			);

			$this->set_type(
				$post['ID'],
				$type
			);
		}

		if ( isset( $attachment['ai_transparency_disclosure'] ) ) {

			$disclosure = sanitize_key(
				$attachment['ai_transparency_disclosure']
			);

			if ( $this->is_valid_disclosure( $disclosure ) ) {

				$this->set_disclosure(
					$post['ID'],
					$disclosure
				);
			}
		}

		return $post;
	}

	/**
	 * Check AI type.
	 *
	 * @param string $type AI type.
	 * @return bool
	 */
	public function is_valid_type( $type ) {

		return in_array(
			$type,
			[
				self::TYPE_GENERATED,
				self::TYPE_MANIPULATED,
				self::TYPE_GENERATED_AND_MANIPULATED,
			],
			true
		);
	}

	/**
	 * Check disclosure value.
	 *
	 * @param string $disclosure Disclosure value.
	 * @return bool
	 */
	public function is_valid_disclosure( $disclosure ) {

		return in_array(
			$disclosure,
			[
				self::DISCLOSURE_AUTOMATIC,
				self::DISCLOSURE_REQUIRED,
				self::DISCLOSURE_DISABLED,
			],
			true
		);
	}
}
