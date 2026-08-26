<?php
/**
 * Plugin Name:  AI Transparency
 * Description:  Provides AI content transparency indicators for images.
 * Version:      1.1.0
 * Requires CP:  2.0
 * Requires PHP: 7.4
 * Text Domain:  ai-transparency
 * Domain Path:  /languages
 * Author:       Simone Fioravanti
 * Author URI:   https://software.gieffeedizioni.it
 * Plugin URI:   https://software.gieffeedizioni.it
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'AI_TRANSPARENCY_VERSION', '1.1.0' );
define( 'AI_TRANSPARENCY_FILE', __FILE__ );
define( 'AI_TRANSPARENCY_DIR', plugin_dir_path( __FILE__ ) );
define( 'AI_TRANSPARENCY_URL', plugin_dir_url( __FILE__ ) );

require_once AI_TRANSPARENCY_DIR . 'includes/class-ai-media.php';
require_once AI_TRANSPARENCY_DIR . 'includes/class-ai-frontend.php';
require_once AI_TRANSPARENCY_DIR . 'includes/class-ai-transparency.php';

AI_Transparency::instance();
