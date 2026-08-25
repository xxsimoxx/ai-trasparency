<?php
/**
 * Plugin Name: AI Transparency
 * Description: Provides AI content transparency indicators for images.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: ai-transparency
 */

defined( 'ABSPATH' ) || exit;

define( 'AI_TRANSPARENCY_VERSION', '1.0.0' );
define( 'AI_TRANSPARENCY_FILE', __FILE__ );
define( 'AI_TRANSPARENCY_DIR', plugin_dir_path( __FILE__ ) );
define( 'AI_TRANSPARENCY_URL', plugin_dir_url( __FILE__ ) );

require_once AI_TRANSPARENCY_DIR . 'includes/class-ai-media.php';
require_once AI_TRANSPARENCY_DIR . 'includes/class-ai-frontend.php';
require_once AI_TRANSPARENCY_DIR . 'includes/class-ai-transparency.php';

AI_Transparency::instance();

/*

.ai-generated {
	color: #7c3aed;
}

.ai-manipulated {
	color: #2563eb;
}

.ai-generated-and-manipulated {
	color: #dc2626;
}

*/
