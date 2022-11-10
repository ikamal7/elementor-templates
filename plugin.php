<?php

/*
 * Plugin Name: WP Demo Templates for elementor
 * Plugin URI:
 * Description:
 * Version: 1.0
 * Author: Kamal Hosen
 * Author URI: 
 * Text Domain: 
 * Domain Path: /languages/
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

 final class WP_Demo_Templates{

    public static $instance;

    public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}


    public function define_constant(){
        define( 'WP_TEMPLATES_VERSION', '1.0' );
        define( 'WP_TEMPLATES_FILE', __FILE__ );
        define( 'WP_TEMPLATES_PATH', __DIR__ );
        define( 'WP_TEMPLATES_URL', plugins_url( '', WP_TEMPLATES_FILE ) );
        define( 'WP_TEMPLATES_ASSETS', WP_TEMPLATES_URL . '/assets' );
    }

    public function init() {
        $this->define_constant();


        if(is_user_logged_in()){
            require_once WP_TEMPLATES_PATH . '/inc/library-manager.php';
        }

    }


 }

 function run_demo_templates() {
    WP_Demo_Templates::instance();
 }
add_action('plugins_loaded', 'run_demo_templates');

