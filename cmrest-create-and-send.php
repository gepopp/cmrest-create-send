<?php
/*
Plugin Name:  Campaign Monitor Create And Send
Plugin URI:   https://www.poppgerhard.at/plugins
Description:  Simply create and send E-Mail campaigns from your WordPress backend
Version:      0.2.0
Author:       Popp Gerhard
Author URI:   https://www.poppgerhard.at
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  cmrest
Domain Path:  /languages
*/

namespace cmrest;



// If this file is called directly, abort.
use cmrest\admin\CMREST_boot;

if ( ! defined( 'WPINC' ) ) {
    die;
}

// Setup the basic constants
define('CMREST_VERSION', '0.2.0');
define('CMREST_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('CMREST_URL', trailingslashit( plugin_dir_url(__FILE__)));
define('CMREST_API_URL', 'https://api.createsend.com/api/v3.2/');


// load the translation anyway
add_action( 'init', function(){
    load_plugin_textdomain( 'cmrest', false, basename( dirname( __FILE__ ) ) . '/languages' );
});



// Load the autoloader
require_once trailingslashit(__DIR__ ) . 'autoloader.php';

// Boot the Plugin
new CMREST_boot();
