<?php
/*
Plugin Name: Announcer
Plugin URI: https://www.aakashweb.com/wordpress-plugins/announcer/
Description: Add notification bar to your site and display any message like welcome message, promotions, coupons, news banner to the top/bottom of the page.
Author: Aakash Chakravarthy
Version: 6.1
Author URI: https://www.aakashweb.com/
Text Domain: announcer
Domain Path: /languages
*/

define( 'ANCR_VERSION', '6.1' );
define( 'ANCR_PATH', plugin_dir_path( __FILE__ ) ); // All have trailing slash
define( 'ANCR_URL', plugin_dir_url( __FILE__ ) );
define( 'ANCR_ADMIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) . 'admin' ) );
define( 'ANCR_BASE_NAME', plugin_basename( __FILE__ ) );
define( 'ANCR_POST_TYPE', 'announcer' );

final class Announcer{

    static public $announcements = array();

    public static function init(){

        self::includes();

        add_action( 'plugins_loaded', array( __CLASS__, 'load_text_domain' ) );

    }

    public static function includes(){

        include_once( ANCR_PATH . 'includes/settings.php' );
        include_once( ANCR_PATH . 'includes/utilities.php' );
        include_once( ANCR_PATH . 'includes/location-rules/index.php' );
        include_once( ANCR_PATH . 'includes/display.php' );

        include_once( ANCR_PATH . 'admin/admin.php' );
        include_once( ANCR_PATH . 'admin/edit.php' );
        include_once( ANCR_PATH . 'admin/manage.php' );
        include_once( ANCR_PATH . 'admin/settings-form.php' );
        include_once( ANCR_PATH . 'admin/fields.php' );

    }

    public static function get_announcements(){

        if( !empty( self::$announcements ) ){
            return self::$announcements;
        }

        $announcements = array();
        $announcement_posts = get_posts(array(
            'post_type' => ANCR_POST_TYPE,
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'asc',
            'post_status' => 'publish'
        ));

        foreach( $announcement_posts as $index => $post ){
            $announcements[ $post->ID ] = array(
                'content' => $post->post_content,
                'settings' => ANCR_Settings::get( $post->ID )
            );
        }

        self::$announcements = $announcements;

        return $announcements;

    }

    public static function load_text_domain(){

        load_plugin_textdomain( 'announcer', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );

    }

}

Announcer::init();

?>