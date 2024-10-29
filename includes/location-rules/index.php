<?php

namespace ANCR;

if ( ! defined( 'ABSPATH' ) ){
    exit;
}

class Location_Rules{

    private $id;

    private $url;

    private $ajax_url;

    private $ajax_handler;

    private $version;

    function __construct(){
        
        $this->id = 'announcer';
        $this->url = plugin_dir_url( __FILE__ );
        $this->ajax_url = get_admin_url() . 'admin-ajax.php';
        $this->ajax_handler = $this->id . '_location_rules';
        $this->version = ANCR_VERSION;

    }
    
    function add_ajax_handler(){
        
        add_action( 'wp_ajax_' . $this->ajax_handler, array( $this, 'selectors_ajax' ) );
        
    }
    
    function enqueue_resources(){
        
        wp_enqueue_style( 'ancr_lr-location-rules', $this->url . 'css/style.css', array(), $this->version );
        wp_enqueue_script( 'ancr_lr-location-rules', $this->url . 'js/script.js', array( 'jquery' ), $this->version );
        
        add_action( 'admin_footer', array( $this, 'rules_list_js_helper' ) );
        
    }
    
    function rules_list(){
        
        //Default rules
        $rules = apply_filters( 'ancr_lr_mod_rules', array(
            'single' => array( 
                'name' => 'Single post',
                'callback' => array( $this, 'rule_is_single' ),
                'placeholder' => __( 'Select specific posts', 'announcer' ),
                'helper' => 1,
                'children' => array( 'has-category', 'has-tag', 'post-type', 'has-term', 'language-wpml' )
            ),
            
            'page' => array(
                'name' => 'Page',
                'callback' => array( $this, 'rule_is_page' ),
                'placeholder' => __( 'Select specific pages', 'announcer' ),
                'helper' => 1,
                'children' => array( 'has-category', 'has-tag', 'post-type', 'has-term', 'language-wpml' )
            ),
            
            'home' => array(
                'name' => 'Home page',
                'callback' => array( $this, 'rule_is_home' ),
                'helper' => 0,
                'children' => array( 'in-excerpt', 'post-type', 'has-category', 'has-tag', 'has-term', 'language-wpml' )
            ),
            
            'front-page' => array(
                'name' => 'Front page',
                'callback' => array( $this, 'rule_is_frontpage' ),
                'helper' => 0,
                'children' => array( 'in-excerpt', 'post-type', 'has-category', 'has-tag', 'has-term', 'language-wpml' )
            ),
            
            'sticky' => array(
                'name' => 'Sticky posts',
                'callback' => array( $this, 'rule_is_sticky' ),
                'helper' => 0
            ),

            'search' => array(
                'name' => 'Search page',
                'callback' => array( $this, 'rule_is_search' ),
                'helper' => 0
            ),
            
            'post-type' => array(
                'name' => 'Post type',
                'callback' => array( $this, 'rule_post_type' ),
                'placeholder' => 'Select available post type',
                'helper' => 1,
                'children' => array( 'has-category', 'has-tag', 'has-term', 'language-wpml' )
            ),
            
            'post-format' => array(
                'name' => 'Post format',
                'callback' => array( $this, 'rule_is_post_format' ),
                'placeholder' => 'Select post formats',
                'helper' => 1,
            ),
            
            'archive' => array(
                'name' => 'Archive pages',
                'callback' => array( $this, 'rule_is_archive' ),
                'helper' => 0,
                'children' => array( 'category', 'tag', 'date', 'taxonomy' )
            ),
            
            'category' => array(
                'name' => 'Category archive page',
                'callback' => array( $this, 'rule_is_category' ),
                'placeholder' => __( 'Select available category archive pages', 'announcer' ),
                'helper' => 1
            ),
            
            'tag' => array(
                'name' => 'Tags archive page',
                'callback' => array( $this, 'rule_is_tag' ),
                'placeholder' => __( 'Select available tag archive pages', 'announcer' ),
                'helper' => 1
            ),
            
            'taxonomy' => array(
                'name' => 'Taxonomy archive page',
                'callback' => array( $this, 'rule_is_taxonomy' ),
                'placeholder' => __( 'Select available taxonomy archive pages', 'announcer' ),
                'children' => array( 'has-term' ),
                'helper' => 1
            ),
            
            'date' => array(
                'name' => 'Date archive page',
                'callback' => array( $this, 'rule_is_date' ),
                'helper' => 0
            ),
            
            'not-found' => array(
                'name' => '404 page',
                'callback' => array( $this, 'rule_is_404' ),
                'helper' => 0
            ),
            
            'has-category' => array(
                'name' => 'Categories of post',
                'callback' => array( $this, 'rule_has_category' ),
                'placeholder' => __( 'Select available categories', 'announcer' ),
                'helper' => 1
            ),
            
            'has-tag' => array(
                'name' => 'Tags of post',
                'callback' => array( $this, 'rule_has_tag' ),
                'placeholder' => __( 'Select available tags', 'announcer' ),
                'helper' => 1
            ),
            
            'has-term' => array(
                'name' => 'Terms of post',
                'callback' => array( $this, 'rule_has_term' ),
                'placeholder' => __( 'Select available terms', 'announcer' ),
                'helper' => 1
            ),

            'language-wpml' => array(
                'name' => 'WPML post language',
                'callback' => array( $this, 'rule_language_wpml' ),
                'placeholder' => __( 'Select WPML language', 'announcer' ),
                'helper' => 1
            ),

        ));
        
        return $rules;
        
    }
    
    function rules_list_js_helper(){
        
        $rules = $this->rules_list();
        
        echo '<script>
        var ancr_lr_' . $this->id . ' = {
            ajax_url: "' . $this->ajax_url . '",
            ajax_handler: "' . $this->ajax_handler . '",
            rules: ' . wp_json_encode( $rules ) . ',
            nonce: "' . wp_create_nonce( 'ancr_lr_nonce' ) . '"
        };
        </script>';
        
    }
    
    function check_rule( $rule_wrap ){
        
        $rule_wrap = wp_parse_args( $rule_wrap, array(
            'type' => 'show_all',
            'rule' => 'W10='
        ));
        
        $type = $rule_wrap[ 'type' ];
        $group = json_decode( base64_decode( $rule_wrap[ 'rule' ] ) );
        
        if( $type == 'show_all' ){
            return 1;
        }else if( $type == 'hide_all' ){
            return 0;
        }
        
        if( empty( $group ) ){
            return 0;
        }
        
        $or_flag = 0;
        foreach( $group as $rules ){
            
            $and_flag = 1;
            foreach( $rules as $rule ){
                $rule_answer = $this->exe_rule( $rule );
                
                if( $rule_answer && $and_flag ){
                    $and_flag = 1;
                }else{
                    $and_flag = 0;
                }
            }
            
            if( $and_flag || $or_flag ){
                $or_flag = 1; // can display;
            }else{
                $or_flag = 0; // cannot display;
            }
            
        }
        
        if( $type == 'show_selected' ){
            return $or_flag;
        }else{
            return !$or_flag;
        }
        
    }
    
    function exe_rule( $rule ){
        
        $rules = $this->rules_list();
        
        $answer = 0;
        
        if( is_callable( $rules[ $rule[0] ][ 'callback' ] ) ){
            $answer = call_user_func_array( $rules[ $rule[ 0 ] ][ 'callback' ], array( 2, $rule[ 2 ] ) ); # Mode 2
        }
        
        if( $rules[ $rule[ 0 ] ][ 'helper' ] != 0 ){
            if( $rule[ 1 ] == 'equal' ){
                return $answer;
            }else{
                return !$answer;
            }
        }else{
            return $answer;
        }
        
    }
    
    function selectors_ajax(){
        
        check_ajax_referer( 'ancr_lr_nonce' );
        
        if( !current_user_can( 'edit_posts' ) ){
            wp_die();
        }

        $rules = $this->rules_list();
        $rule_id = $_GET[ 'rule_id' ];
        $selected = $_GET[ 'selected' ];
        
        if( !array_key_exists( $rule_id, $rules ) ){
            die( __( 'Invalid rule id !', 'announcer' ) );
        }
        
        // Mode 1: Get selectors list
        // Mode 2: Check rule
        if( isset( $rules[ $rule_id ][ 'callback' ] ) && is_callable( $rules[ $rule_id ][ 'callback' ] ) ){
            $list = call_user_func_array( $rules[ $rule_id ][ 'callback' ], array( 1, '' ) ); # Mode 1
        }else{
            die( __( 'No selections supported for this page !', 'announcer' ) );
        }
        
        if( empty( $list ) )
            die();
        
        $selSplit = array_filter( array_map( 'trim', explode( ',', $selected ) ) );

        if( is_array( $selSplit ) ){
            foreach ( $list as $k => $v ){
                $isCheck = in_array( $k, $selSplit ) ? 'checked="selected"' : '';
                echo '<label><input type="checkbox" ' . $isCheck . ' value="' . $k . '"> ' . $v . '</label><br/>';
            }
        }
        
        die();
        
    }
    
    function display_rules( $id, $values = array() ){
        
        $types = array(
            'show_all' => __( 'Show in all pages', 'announcer' ),
            'hide_all' => __( 'Hide in all pages', 'announcer' ),
            'show_selected' => __( 'Show in selected pages', 'announcer' ),
            'hide_selected' => __( 'Hide in selected pages', 'announcer' )
        );
        
        echo '<div class="ancr_lr_rules_wrap" data-id="' . esc_attr( $this->id ) . '">';
        echo '<div class="ancr_lr_rules_sec">';
        
        echo '<div class="ancr_lr_rules_type">';
        echo $this->field( 'radio', array(
            'name' => $id . '[type]',
            'list' => $types,
            'value' => $values['type'],
            'default' => 'show_all',
            'class' => 'ancr_lr_type'
        ));
        echo '</div>';
        
        // Set default pages to rule
        if( !isset( $values['rule'] ) ){
            $values['rule'] = 'W10='; // [] - Default base64 value for no rule
        }
        
        $values['rule'] = json_decode( base64_decode( $values['rule'] ) );
        
        echo '<div class="ancr_lr_rules_inner">';
        echo '<p class="ancr_lr_rule_info">' . esc_html__( 'No page rules are added. Template will be hidden everywhere', 'announcer' ) . '</p>';
        
        echo '<div class="ancr_lr_rules_box">';
        if( is_array( $values['rule'] ) ){
            foreach( $values['rule'] as $grp ){
                echo '<div class="ancr_lr_group_wrap">';
                foreach( $grp as $rle ){
                    echo $this->rules_template( $rle, 0 );
                }
                echo '</div>';
            }
        }
        echo '</div>';
        
        echo '<a href="#" class="button-primary ancr_lr_group_add" title="' . esc_attr__( 'Add another page', 'announcer' ) . '">  AND  </a>';
        echo '</div>';
        
        echo '<div class="hidden">';
            echo '<input type="hidden" name="' . esc_attr( $id ) . '[rule]" class="ancr_lr_rule_value" />';
            echo '<div class="ancr_lr_rules_temp">' . $this->rules_template( array( '', '', '' ), 1 ) . '</div>';
        echo '</div>';
        
        echo '</div>'; // Close section 1

        echo '</div>';
        
    }
    
    function rules_template( $val, $grp ){

        $rules = $this->rules_list();
        
        $operators = array(
            array( 'equal', 'is' ),
            array( 'not-equal', 'is not' )
        );
        
        $loc_pages = '';
        $loc_operators = '';
        
        foreach( $rules as $k => $v ){
            $s = selected( $k, $val[ 0 ], false );
            
            if( isset( $v[ 'helper' ] ) ){
                $h = 'data-helper="' . esc_attr( $v[ 'helper' ] ) . '"';
            }
            
            $p = isset( $v[ 'placeholder' ] ) ? ' data-placeholder="' . esc_attr( $v[ 'placeholder' ] ) . '"' : '';
            $loc_pages .= '<option value="' . esc_attr( $k ) . '" ' . $s . $h . $p . '>' . esc_html( $v[ 'name' ] ) . '</option>';
        }
        
        foreach( $operators as $k => $v ){
            $s = selected( $v[0], $val[1], false );
            $loc_operators .= '<option value="' . esc_attr( $v[0] ) . '" ' . $s . '>' . esc_html( $v[1] ) . '</option>';
        }
        
        $rule = '<div class="ancr_lr_rule_wrap"><select class="ancr_lr_page">' . $loc_pages . '</select><select class="ancr_lr_operator">' . $loc_operators . '</select><input type="text" class="ancr_lr_value" value="' . esc_attr( $val[2] ) . '" placeholder="" title="' . esc_attr__( 'Leave empty to show in all', 'announcer' ) . '"/><a href="#" class="button ancr_lr_rule_add" title="' . esc_attr__( 'Add another criteria to match', 'announcer' ) . '">+</a><a href="#" class="button ancr_lr_rule_remove" title="' . esc_attr__( 'Remove criteria', 'announcer' ) . '">-</a></div>';
        
        if( $grp ) return '<div class="ancr_lr_group_wrap">' . $rule . '</div>';
        else return $rule;
    }
    
    function array_it( $ids ){
        
        return array_filter( array_map( 'trim', explode( ',', $ids ) ) );
        
    }
    
    function rule_is_single( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
            $list = array();

            $posts = get_posts( 'posts_per_page=-1&post_type=post&post_status=publish,draft,private,future' );
            if ( !empty( $posts ) ){
                foreach ( $posts as $post ){
                    $list[ $post->ID ] = $post->post_title;
                }
                return $list;
            }else{
                die( __( 'No posts !', 'announcer' ) );
            }
            
        }elseif( $mode == 2 ){ // Rule check
            return is_single( $this->array_it( $ids ) );
        }
        
    }
    
    
    function rule_is_page( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
            $list = array();

            $pages = get_posts( 'posts_per_page=-1&post_type=page&post_status=publish,draft,private,future' );
            if ( !empty( $pages ) ){
                foreach ( $pages as $page ){
                    $list[ $page->ID ] = $page->post_title;
                }
                return $list;
            }else{
                die( __( 'No pages !', 'announcer' ) );
            }
            
            return $list;
            
        }elseif( $mode == 2 ){ // Rule check
            return is_page( $this->array_it( $ids ) );
        }
        
    }
    
    function rule_post_type( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
        
            return get_post_types( array( 'public' => true ) );
            
        }elseif( $mode == 2 ){ // Rule check
            
            $post_types = $this->array_it( $ids );
            return in_array( get_post_type(), $post_types );
            
        }
        
    }
    
    function rule_is_post_format( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
        
            return array(
                'standard' => 'Standard/No post format set',
                'aside' => 'Aside',
                'chat' => 'Chat',
                'gallery' => 'Gallery',
                'link' => 'Link',
                'image' => 'Image',
                'quote' => 'Quote',
                'status' => 'Status',
                'video' => 'Video',
                'audio' => 'Audio'
            );
            
        }elseif( $mode == 2 ){ // Rule check
            
            $selected_post_formats = $this->array_it( $ids );
            $format = get_post_format() ? : 'standard';
            return in_array( $format, $selected_post_formats );
            
        }
        
    }
    
    function rule_is_archive( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
        }elseif( $mode == 2 ){ // Rule check
            return is_archive();
        }
        
    }
    
    function rule_is_category( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
            
            $list = array();
            $cats = get_categories();
            
            if( !empty( $cats ) ){
                foreach( $cats as $cat ){
                    $list[ $cat->slug ] = $cat->name;
                }
                return $list;
            }else{
                die( __( 'No categories !', 'announcer' ) );
            }
            
        }elseif( $mode == 2 ){ // Rule check
            return is_category( $this->array_it( $ids ) );
        }
        
    }
    
    
    function rule_is_tag( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
            
            $list = array();
            $tags = get_tags();
            
            if( !empty( $tags ) ){
                foreach( $tags as $tag ){
                    $list[ $tag->slug ] = $tag->name;
                }
                return $list;
            }else{
                die( __( 'No tags !', 'announcer' ) );
            }
            
        }elseif( $mode == 2 ){ // Rule check
            return is_tag( $this->array_it( $ids ) );
        }
        
    }
    
    function rule_is_taxonomy( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
            
            $list = array();
            $taxonomies = get_taxonomies();
            
            if( !empty( $taxonomies ) ){
                foreach( $taxonomies as $tax => $tax_name ){
                    $list[ $tax ] = $tax_name;
                }
                return $list;
            }else{
                die( __( 'No taxonomies !', 'announcer' ) );
            }
            
        }elseif( $mode == 2 ){ // Rule check
            return is_tax( $this->array_it( $ids ) );
        }
        
    }

    function rule_is_date( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
        }elseif( $mode == 2 ){ // Rule check
            return is_date();
        }
        
    }
    
    function rule_has_category( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
            
            $list = array();
            $cats = get_categories();
            
            if( !empty( $cats ) ){
                foreach( $cats as $cat ){
                    $list[ $cat->slug ] = $cat->name;
                }
                return $list;
            }else{
                die( __( 'No categories !', 'announcer' ) );
            }
            
        }elseif( $mode == 2 ){ // Rule check
            return has_category( $this->array_it( $ids ) );
        }
        
    }
    
    function rule_has_tag( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
            
            $list = array();
            $tags = get_tags();
            
            if( !empty( $tags ) ){
                foreach( $tags as $tag ){
                    $list[ $tag->slug ] = $tag->name;
                }
                return $list;
            }else{
                die( __( 'No tags !', 'announcer' ) );
            }
            
        }elseif( $mode == 2 ){ // Rule check
            return has_tag( $this->array_it( $ids ) );
        }
        
    }
    
    function rule_has_term( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
            
            $list = array();
            $terms = get_terms();
            
            if( !empty( $terms ) ){
                foreach( $terms as $term ){
                    $list[ $term->slug ] = $term->name . ' (' . $term->taxonomy . ')';
                }
                return $list;
            }else{
                die( __( 'No terms !', 'announcer' ) );
            }
            
        }elseif( $mode == 2 ){ // Rule check
            global $post;
            
            if( $post == null ){
                return false;
            }
            
            $all_tax = get_object_taxonomies( $post->post_type );
            foreach( $all_tax as $tax ){
                if( has_term( $this->array_it( $ids ), $tax ) ){
                    return true;
                }
            }
            return false;
        }
        
    }
    
    function rule_is_home( $mode, $ids = '' ){
        
        if( $mode == 1 ){
        }elseif( $mode == 2 ){
            return is_home();
        }
        
    }
    
    function rule_is_frontpage( $mode, $ids = '' ){
        
        if( $mode == 1 ){
        }elseif( $mode == 2 ){
            return is_front_page();
        }
        
    }
    
    function rule_is_sticky( $mode, $ids = '' ){
        
        if( $mode == 1 ){
        }elseif( $mode == 2 ){
            return is_sticky();
        }
        
    }

    function rule_is_search( $mode, $ids = '' ){
        
        if( $mode == 1 ){
        }elseif( $mode == 2 ){
            return is_search();
        }
        
    }
    
    function rule_is_404( $mode, $ids = '' ){
        
        if( $mode == 1 ){ // Rule selectors
        }elseif( $mode == 2 ){ // Rule check
            return is_404();
        }
        
    }
    
    function rule_language_wpml( $mode, $ids = '' ){

        if( $mode == 1 ){ // Rule selectors

            $languages = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );
            $list = array();

            if ( $languages ) {
                foreach ( $languages as $language ) {
                    $code = $language['language_code'];
                    $name = $language['translated_name'];
                    $list[ $code ] = $name;
                }
            }

            return $list;

        }elseif( $mode == 2 ){ // Rule check
            $current_lang = apply_filters( 'wpml_current_language', NULL );
            $selected_langs = $this->array_it( $ids );

            return in_array( $current_lang, $selected_langs );
        }

    }
    
    function field( $field_type, $params = array() ){
        
        $defaults = array(
            
            'select' => array(
                'id' => '',
                'class' => '',
                'name' => '',
                'list' => array(),
                'value' => '',
                'default' => '',
                'helper' => '',
                'custom' => ''
            ),
            
            'radio' => array(
                'id' => '',
                'class' => '',
                'name' => '',
                'list' => array(),
                'value' => '',
                'default' => '',
                'helper' => '',
                'custom' => ''
            )
            
        );
        
        $params = wp_parse_args( $params, $defaults[ $field_type ] );
        $field_html = '';
        
        extract( $params, EXTR_SKIP );
        
        $id_attr = empty( $id ) ? '' : ' id="' . esc_attr( $id ) . '"';

        switch( $field_type ){
            
            case 'select':
                $field_html .= '<select name="' . esc_attr( $name ) . ' class="' . esc_attr( $class ) . '"' . $id_attr . $custom . '>';
                foreach( $list as $k => $v ){
                    $field_html .= '<option value="' . esc_attr( $k ) . '" ' . selected( $value, $k, false ) . '>' . esc_html( $v ) . '</option>';
                }
                $field_html .= '</select>';
            break;
            
            case 'radio':
                foreach( $list as $k => $v ){
                    $field_html .= '<label' . $custom . '><input type="radio" name="' . esc_attr( $name ) . '" class="' . esc_attr( $class ) . '" value="' . esc_attr( $k ) . '"' . $id_attr . ' ' . checked( $value, $k, false ) . ' />&nbsp;' . esc_html( $v ) . '</label>';
                }
            break;
            
        }
        
        if( !empty( $helper ) )
            $field_html .= '<p class="description">' . wp_kses_post( $helper ) . '</p>';
        
        return $field_html;
        
    }
    
}

?>