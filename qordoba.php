<?php
/*
Plugin Name: Qordoba
Plugin URI: http://qordoba.com/
Description: Translate content of your multilingual website with Qordoba.
Version: 0.1.0
Author: Qordoba
Author URI: http://qordoba.com
Text Domain: qordoba
*/

# quit silently if the file is called directly
if ( !function_exists('add_action') ) {
  exit;
}

define('QORDOBA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QORDOBA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QORDOBA_VERSION', '0.1.0');
define('QORDOBA_API_URL', 'https://app.qordoba.com/api/');

if (!defined('PLL_LINGOTEK_AD')) {
  define('PLL_LINGOTEK_AD', false);
}

require_once QORDOBA_PLUGIN_DIR . '/vendor/autoload.php';

require_once QORDOBA_PLUGIN_DIR . 'class.Qordoba.php';
require_once QORDOBA_PLUGIN_DIR . '/includes/class.Qordoba_Object.php';
require_once QORDOBA_PLUGIN_DIR . '/includes/class.Qordoba_Options.php';
require_once QORDOBA_PLUGIN_DIR . '/includes/class.Qordoba_Actions.php';
require_once QORDOBA_PLUGIN_DIR . '/modules/class.Qordoba_Module.php';
require_once QORDOBA_PLUGIN_DIR . '/modules/class.Qordoba_Module_Polylang.php';

add_action('init', 'qor', 100);

function qor() {
  return Qordoba::getInstance();
}
