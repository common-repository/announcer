<?php

if( ! defined( 'ABSPATH' ) ) exit;

class ANCR_Admin{

    public static $location_rules;

    public static function init(){

        add_action( 'init', array( __CLASS__, 'register_post_type' ), 0 );

        add_action( 'init', array( __CLASS__, 'register_taxonomy' ), 0 );

        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

        add_filter( 'plugin_action_links_' . ANCR_BASE_NAME, array( __CLASS__, 'action_links' ) );

        add_action( 'admin_menu', array( __CLASS__, 'upgrade_menu' ) );

        add_action( 'admin_footer', array( __CLASS__, 'admin_footer' ) );

        self::$location_rules = new \ANCR\Location_Rules();
        self::$location_rules->add_ajax_handler();

    }

    public static function register_post_type(){

        $labels = array(
            'name'                  => _x( 'Announcer', 'Post Type General Name', 'announcer' ),
            'singular_name'         => _x( 'Announcement', 'Post Type Singular Name', 'announcer' ),
            'menu_name'             => __( 'Announcer', 'announcer' ),
            'name_admin_bar'        => __( 'Announcer', 'announcer' ),
            'archives'              => __( 'Announcement Archives', 'announcer' ),
            'attributes'            => __( 'Announcement Attributes', 'announcer' ),
            'parent_item_colon'     => __( 'Parent announcement:', 'announcer' ),
            'all_items'             => __( 'All announcements', 'announcer' ),
            'add_new_item'          => __( 'Add New announcement', 'announcer' ),
            'add_new'               => __( 'Add announcement', 'announcer' ),
            'new_item'              => __( 'New announcement', 'announcer' ),
            'edit_item'             => __( 'Edit announcement', 'announcer' ),
            'update_item'           => __( 'Update announcement', 'announcer' ),
            'view_item'             => __( 'View announcement', 'announcer' ),
            'view_items'            => __( 'View announcements', 'announcer' ),
            'search_items'          => __( 'Search announcement', 'announcer' ),
            'not_found'             => __( 'Not found', 'announcer' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'announcer' ),
            'featured_image'        => __( 'Featured Image', 'announcer' ),
            'set_featured_image'    => __( 'Set featured image', 'announcer' ),
            'remove_featured_image' => __( 'Remove featured image', 'announcer' ),
            'use_featured_image'    => __( 'Use as featured image', 'announcer' ),
            'insert_into_item'      => __( 'Insert into item', 'announcer' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'announcer' ),
            'items_list'            => __( 'Announcements list', 'announcer' ),
            'items_list_navigation' => __( 'Announcements list navigation', 'announcer' ),
            'filter_items_list'     => __( 'Filter Announcements list', 'announcer' ),
        );

        $args = array(
            'label'                 => __( 'Announcement', 'announcer' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'page-attributes' ),
            'taxonomies'            => array( 'ancr_tag' ),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 25,
            'menu_icon'             => 'dashicons-microphone',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'page',
            'show_in_rest'          => false,
        );

        register_post_type( ANCR_POST_TYPE, $args );

    }

    public static function register_taxonomy(){

        $labels = array(
            'name'                       => _x( 'Tags', 'Taxonomy General Name', 'announcer' ),
            'singular_name'              => _x( 'Tag', 'Taxonomy Singular Name', 'announcer' ),
            'menu_name'                  => __( 'Tags', 'announcer' ),
            'all_items'                  => __( 'All Tags', 'announcer' ),
            'parent_item'                => __( 'Parent Tag', 'announcer' ),
            'parent_item_colon'          => __( 'Parent Tag:', 'announcer' ),
            'new_item_name'              => __( 'New Tag Name', 'announcer' ),
            'add_new_item'               => __( 'Add New Tag', 'announcer' ),
            'edit_item'                  => __( 'Edit Tag', 'announcer' ),
            'update_item'                => __( 'Update Tag', 'announcer' ),
            'view_item'                  => __( 'View Tag', 'announcer' ),
            'separate_items_with_commas' => __( 'Separate tags with commas', 'announcer' ),
            'add_or_remove_items'        => __( 'Add or remove tags', 'announcer' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'announcer' ),
            'popular_items'              => __( 'Popular Tags', 'announcer' ),
            'search_items'               => __( 'Search Tags', 'announcer' ),
            'not_found'                  => __( 'Not Found', 'announcer' ),
            'no_terms'                   => __( 'No tags', 'announcer' ),
            'items_list'                 => __( 'Tags list', 'announcer' ),
            'items_list_navigation'      => __( 'Tags list navigation', 'announcer' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => false,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => false,
            'show_tagcloud'              => false,
            'show_in_rest'               => false,
        );

        register_taxonomy( 'ancr_tag', array( ANCR_POST_TYPE ), $args );
        
    }

    public static function is_ancr_admin_page(){

        $screen = get_current_screen();

        if( $screen && $screen->post_type == ANCR_POST_TYPE ){
            return true;
        }else{
            return false;
        }

    }

    public static function inline_js_variables(){

        return array(
            'ancr_version' => ANCR_VERSION,
            'ajax_url' => get_admin_url() . 'admin-ajax.php',
            'screen' => get_current_screen(),
            'editor_placeholder' => __( 'Enter your announcement message here', 'announcer' )
        );

    }

    public static function enqueue_scripts( $hook ){

        wp_enqueue_style( 'ancr-icon-css', ANCR_ADMIN_URL . 'css/menu-icon.css', array(), ANCR_VERSION );

        if( !self::is_ancr_admin_page() ){
            return false;
        }

        wp_enqueue_style( 'ancr-admin-css', ANCR_ADMIN_URL . 'css/style.css', array(), ANCR_VERSION );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'ancr-admin-js', ANCR_ADMIN_URL . 'js/script.js', array( 'jquery' ), ANCR_VERSION );

        wp_localize_script( 'ancr-admin-js', 'ANCR_VARS', self::inline_js_variables() );

        $screen = get_current_screen();
        if( $screen->base != 'edit' ){
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            self::$location_rules->enqueue_resources();

            wp_enqueue_script( 'ancr-datetime-js', ANCR_ADMIN_URL . 'js/datetime-picker/jquery.datetimepicker.full.min.js', array( 'jquery', 'ancr-admin-js' ), ANCR_VERSION );
            wp_enqueue_style( 'ancr-datetime-css', ANCR_ADMIN_URL . 'js/datetime-picker/jquery.datetimepicker.min.css', array( 'ancr-admin-css' ), ANCR_VERSION );
        }

    }

    public static function action_links( $links ){
        array_unshift( $links, '<a href="'. esc_url( admin_url( 'edit.php?post_type=announcer') ) .'">' . esc_html__( 'Manage', 'announcer' ) . '</a>' );
        array_unshift( $links, '<a href="https://www.aakashweb.com/wordpress-plugins/announcer/?utm_source=admin&utm_medium=action_link&utm_campaign=ancr-pro#pro" target="_blank"><b>' . esc_html__( 'Upgrade', 'announcer' ) . '</b></a>' );
        return $links;
    }

    public static function upgrade_menu(){

        add_submenu_page( 'edit.php?post_type=announcer', 'Announcer - Upgrade', '<span style="color: #ff8c29" class="ancr_upgrade_link">Upgrade to PRO</span>', 'manage_options', 'https://www.aakashweb.com/wordpress-plugins/announcer/?utm_source=admin&utm_medium=menu&utm_campaign=ancr-pro#pro', null );

    }

    public static function admin_footer(){

        echo '<script>try{ (function($){
            $(document).ready(function(){ $(".ancr_upgrade_link").parent().attr("target", "_blank"); });
        })(jQuery); }catch(e){console.log(e);}
        </script>';

    }

    public static function sanitize_post_array( $data ){

        $sanitized_data = array();

        foreach( $data as $name => $val ){
            if( is_array( $val ) ){
                $sanitized_data[ $name ] = self::sanitize_post_array( $val );
            }else{
                $sanitized_data[ $name ] = sanitize_text_field( $val );
            }
        }

        return $sanitized_data;

    }

    public static function pivot_array( $data ){

        $result = array();

        foreach( $data as $key => $values ){
            for( $i = 0; $i < count($values); $i++ ){
                $result[$i][$key] = $values[$i];
            }
        }

        return $result;

    }

}

ANCR_Admin::init();

?>