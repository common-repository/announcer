<?php

if( ! defined( 'ABSPATH' ) ) exit;

class ANCR_Settings_Form{

    public static $settings = [];

    public static function init(){

        add_action('post_submitbox_minor_actions', array( __CLASS__, 'status_form' ) );

    }

    public static function forms(){

        $forms = [
            'cta' => [
                'name' => __( 'Call to Actions', 'announcer' ),
                'callback' => [ __CLASS__, 'cta' ],
                'icon' => 'dashicons-yes'
            ],

            'display' => [
                'name' => __( 'Display', 'announcer' ),
                'callback' => [ __CLASS__, 'display' ],
                'icon' => 'dashicons-visibility'
            ],

            'position' => [
                'name' => __( 'Position', 'announcer' ),
                'callback' => [ __CLASS__, 'position' ],
                'icon' => 'dashicons-archive'
            ],

            'layout' => [
                'name' => __( 'Layout', 'announcer' ),
                'callback' => [ __CLASS__, 'layout' ],
                'icon' => 'dashicons-layout'
            ],

            'design' => [
                'name' => __( 'Design', 'announcer' ),
                'icon' => 'dashicons-admin-customizer',
                'sections' => [
                    'bar' => [
                        'name' => __( 'Bar', 'announcer' ),
                        'callback' => [ __CLASS__, 'design_bar' ]
                    ],
                    'primary_btn' => [
                        'name' => __( 'Primary button', 'announcer' ),
                        'callback' => [ __CLASS__, 'design_primary_btn' ]
                    ],
                    'secondary_btn' => [
                        'name' => __( 'Secondary button', 'announcer' ),
                        'callback' => [ __CLASS__, 'design_secondary_btn' ]
                    ]
                ]
            ],

            'close' => [
                'name' => __( 'Close', 'announcer' ),
                'icon' => 'dashicons-no-alt',
                'sections' => [
                    'general' => [
                        'name' => __( 'General', 'announcer' ),
                        'callback' => [ __CLASS__, 'close_general' ]
                    ],
                    'keep_closed' => [
                        'name' => __( 'Keep closed', 'announcer' ),
                        'callback' => [ __CLASS__, 'close_keep_closed' ]
                    ]
                ]
            ],

            'location_rules' => [
                'name' => __( 'Location rules', 'announcer' ),
                'callback' => [ __CLASS__, 'location_rules' ],
                'icon' => 'dashicons-location-alt'
            ],

            'pro_tab' => [
                'name' => __( 'More features', 'announcer' ),
                'callback' => [ __CLASS__, 'pro_tab' ],
                'icon' => 'dashicons-star-filled'
            ],

        ];

        return $forms;

    }

    public static function render(){

        global $post;

        self::$settings = ANCR_Settings::get( $post->ID );
        $forms = self::forms();

        echo '<div id="settings_wrap">';

        echo '<div class="mtab_links">';
        foreach( $forms as $form_id => $form_prop ){
            $icon = isset( $form_prop[ 'icon' ] ) ? $form_prop[ 'icon' ] : 'dashicons-admin-generic';
            echo '<a href="#tab_' . $form_id . '"><span class="dashicons ' . $icon . '"></span>' . $form_prop[ 'name' ] . '</a>';
        }
        echo '</div>';

        echo '<div class="mtab_wrap">';
        foreach( $forms as $form_id => $form_prop ){
            echo '<div id="tab_' . $form_id . '">';
            if( array_key_exists( 'callback', $form_prop ) ){
                $callback = $form_prop[ 'callback' ];
                if( is_callable( $callback ) ){
                    call_user_func( $callback );
                }
            }else{
                echo '<div class="stab_links">';
                foreach( $form_prop[ 'sections' ] as $sec_id => $sec_prop ){
                    echo '<a href="#tab_' . $sec_id . '">' . $sec_prop[ 'name' ]  . '</a>';
                }
                echo '</div>';

                echo '<div class="stab_wrap">';
                foreach( $form_prop[ 'sections' ] as $sec_id => $sec_prop ){
                    echo '<div id="tab_' . $sec_id . '">';
                    if( array_key_exists( 'callback', $sec_prop ) && is_callable( $sec_prop[ 'callback' ] ) ){
                        call_user_func( $sec_prop[ 'callback' ] );
                    }
                    echo '</div>';
                }
                echo '</div>';

            }
            echo '</div>';
        }
        echo '</div>';

        wp_nonce_field( 'ancr_post_nonce', 'ancr_nonce' );
        wp_nonce_field( 'ancr_preview_nonce', 'ancr_preview_nonce' );

        echo '</div>';

    }

    public static function status_form( $post ){

        if( !is_object( $post ) || !property_exists( $post, 'post_type' ) || $post->post_type != ANCR_POST_TYPE ){
            return;
        }

        $settings = ANCR_Settings::get( $post->ID );

        echo '<div class="ancr_preview_btn_wrap">';
        echo '<a href="#" class="button ancr_duplicate_btn" title="' . __( 'Duplicate Announcement', 'announcer' ) . '"><span class="dashicons dashicons-admin-page"></span> Duplicate <span class="pro_tag">PRO</span></a>';
        echo '<span class="dashicons dashicons-info-outline ancr_preview_info"></span> <a href="#" class="button ancr_preview_btn">' . __( 'Preview', 'announcer' ) . '</a>';
        echo '</div>';
        
        echo '<div class="ancr_status">';
        echo '<label>' . __( 'Status', 'announcer' ) . '</label>';
        echo ANCR_Fields::field( 'select', [
            'name' => 'settings[status]',
            'list' => [
                'active' => __( 'Active', 'announcer' ),
                'inactive' => __( 'Inactive', 'announcer' )
            ],
            'value' => $settings[ 'status' ]
        ]);
        echo '</div>';

    }

    public static function cta(){

        $settings = self::$settings;

        echo '<label class="field_label">' . __( 'Add buttons to the announcement', 'announcer' ) . '</label>';

        echo '<div id="cta_list">';

        foreach( $settings[ 'cta_buttons' ] as $button ){
            self::template_cta_buttons( $button );
        }

        echo '</div>';

        echo '<p><a href="#" class="button button-primary cta_add">' . __( 'Add button', 'announcer' ) . '</a></p>';

        echo '<script type="text/html" id="tmpl-cta-buttons">';
        self::template_cta_buttons();
        echo '</script>';

        echo '<a class="pro_note" href="https://www.aakashweb.com/wordpress-plugins/announcer/?utm_source=admin&utm_medium=animation&utm_campaign=ancr-pro#pro" target="_blank"><span class="dashicons dashicons-buddicons-groups"></span> Want to add animation to the buttons and grab attention ? Check out the <em>animation feature</em> in the PRO version</a>';

    }

    public static function display(){

        $fields = new ANCR_Form_Builder();
        $settings = self::$settings;

        $fields->start( 'field_display' );
        $fields->heading( __( 'Display the announcement', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'display' ],
            'name' => 'settings[display]',
            'list' => array(
                'immediate' => __( 'Immediately', 'announcer' ),
                'schedule' => __( 'Schedule', 'announcer' ),
                'custom' => __( 'Custom', 'announcer' )
            ),
            'helper' => __( 'Announcement will be inserted in the page, but it will be hidden. Please refer <a href="https://www.aakashweb.com/docs/announcer/js-api/" target="_blank">the announcer JavaScript API documentation</a> to show/hide the announcement on your own.', 'announcer' )
        ));
        $fields->end();

        $timezone_select = '<span class="field_before_text">Timezone</span>';
        $timezone_select .= '<select name="settings[schedule_timezone]">' . wp_timezone_choice( $settings[ 'schedule_timezone' ] ) . '</select>';

        $fields->start( 'field_schedule' );
        $fields->heading( __( 'Schedule duration', 'announcer' ) );
        $fields->field( 'text', array(
            'type' => 'text',
            'value' => $settings[ 'schedule_from' ],
            'name' => 'settings[schedule_from]',
            'class' => 'datetime_picker',
            'autocomplete' => 'off',
            'before_text' => __( 'From', 'announcer' )
        ));
        $fields->field( 'text', array(
            'type' => 'text',
            'value' => $settings[ 'schedule_to' ],
            'name' => 'settings[schedule_to]',
            'class' => 'datetime_picker',
            'autocomplete' => 'off',
            'before_text' => __( 'To', 'announcer' )
        ));
        $fields->field( 'html', $timezone_select );
        $fields->description( __( 'Select the date range between which the announcement should be shown.', 'announcer' ) );
        $fields->end();

        $fields->start( 'field_show_on' );
        $fields->heading( __( 'Show announcement on page', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'show_on' ],
            'name' => 'settings[show_on]',
            'list' => array(
                'page_open' => __( 'Immediate', 'announcer' ),
                'duration' => __( 'After a duration', 'announcer' ),
                'page_scroll' => __( 'After page scroll', 'announcer' )
            )
        ));
        $fields->end();

        $fields->start('show_on after_duration');
        $fields->heading( __( 'Show after duration', 'announcer' ) );
        $fields->field( 'text', array(
            'value' => $settings[ 'show_after_duration' ],
            'type' => 'number',
            'name' => 'settings[show_after_duration]',
            'class' => 'small-text',
            'after_text' => 'seconds'
        ));
        $fields->end();

        $fields->start('show_on after_page_scroll');
        $fields->heading( __( 'Show after page scroll', 'announcer' ) );
        $fields->field( 'text', array(
            'value' => $settings[ 'show_after_scroll' ],
            'type' => 'number',
            'name' => 'settings[show_after_scroll]',
            'class' => 'small-text',
            'after_text' => 'px'
        ));
        $fields->end();

        $fields->start();
        $fields->heading( __( 'On show animation', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'open_animation' ],
            'name' => 'settings[open_animation]',
            'list' => array(
                'none' => __( 'None', 'announcer' ),
                'slide' => __( 'Slide', 'announcer' ),
                'fade' => __( 'Fade', 'announcer' )
            )
        ));
        $fields->end();

        $fields->build();

    }

    public static function position(){

        global $post;
        $fields = new ANCR_Form_Builder();
        $settings = self::$settings;

        $fields->start();
        $fields->heading( __( 'Position of the announcement', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'position' ],
            'name' => 'settings[position]',
            'list' => array(
                'top' => __( 'Top of the page', 'announcer' ),
                'bottom' => __( 'Bottom of the page', 'announcer' )
            )
        ));
        $fields->end();

        $fields->start();
        $fields->heading( __( 'Sticky', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'sticky' ],
            'name' => 'settings[sticky]',
            'list' => array(
                'yes' => __( 'Yes', 'yes' ),
                'no' => __( 'No', 'no' )
            )
        ));
        $fields->description( __( 'Select to stick the announcement to the window.', 'announcer' ) );
        $fields->end();

        $fields->start();
        $fields->heading( __( 'Order', 'announcer' ) );
        $fields->field( 'text', array(
            'type' => 'number',
            'value' => $post->menu_order,
            'name' => 'menu_order'
        ));
        $fields->description( __( 'The order of the announcement when multiple announcements are stacked in a page (ascending order). Leave default to 0 instead.', 'announcer' ) );
        $fields->end();

        $fields->build();

        echo '<a class="pro_note" href="https://www.aakashweb.com/wordpress-plugins/announcer/?utm_source=admin&utm_medium=shortcode&utm_campaign=ancr-pro#pro" target="_blank"><span class="dashicons dashicons-shortcode"></span> Insert the announcement anywhere using <em>Shortcodes</em></a>';

    }

    public static function layout(){

        $fields = new ANCR_Form_Builder();
        $settings = self::$settings;

        $fields->start();
        $fields->heading( __( 'Layout', 'announcer' ) );
        $fields->field( 'image_select', array(
            'value' => $settings[ 'layout' ],
            'name' => 'settings[layout]',
            'list' => array(
                'same_row' => [ __( 'Same row', 'announcer' ), 'same-row.svg' ],
                'separate_column' => [ __( 'Separate column', 'announcer' ), 'separate-column.svg' ],
                'separate_row' => [ __( 'Separate row', 'announcer' ), 'separate-row.svg' ],
                'ticker' => [ __( 'Ticker (Horizontal scroll) (New)', 'announcer' ), 'ticker.svg' ]
            )
        ));
        $fields->end();

        $fields->start('normal_layout');
        $fields->heading( __( 'Content alignment', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'align_content' ],
            'name' => 'settings[align_content]',
            'list' => array(
                'left' => __( 'Left', 'announcer' ),
                'center' => __( 'Center', 'announcer' ),
                'right' => __( 'Right', 'announcer' )
            )
        ));
        $fields->description( __( 'Select the text alignment of the announcement message', 'announcer' ) );
        $fields->end();

        $fields->start('normal_layout');
        $fields->heading( __( 'Container width', 'announcer' ) );
        $fields->field( 'text', array(
            'value' => $settings[ 'container_width' ],
            'name' => 'settings[container_width]'
        ));
        $fields->description( __( 'The width of the announcement container. Include units like px, %. Example: 1000px.', 'announcer' ) );
        $fields->end();

        $fields->start('ticker_layout');
        $fields->heading( __( 'Ticker speed', 'announcer' ) );
        $fields->field( 'text', array(
            'value' => $settings[ 'ticker_speed' ],
            'name' => 'settings[ticker_speed]',
            'class' => 'small-text',
            'after_text' => 'seconds'
        ));
        $fields->description( __( 'The time in seconds it takes for the announcement to scroll from one end to another.', 'announcer' ) );
        $fields->end();

        $fields->start('ticker_layout');
        $fields->heading( __( 'Ticker direction', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'ticker_direction' ],
            'name' => 'settings[ticker_direction]',
            'list' => array(
                'right_left' => __( 'Right to Left', 'announcer' ),
                'left_right' => __( 'Left to Right', 'announcer' )
            )
        ));
        $fields->description( __( 'The direction of the ticker scroll.', 'announcer' ) );
        $fields->end();

        $fields->start('ticker_layout');
        $fields->heading( __( 'Pause ticker on hover', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'ticker_pause' ],
            'name' => 'settings[ticker_pause]',
            'list' => array(
                'yes' => __( 'Yes', 'announcer' ),
                'no' => __( 'No', 'announcer' )
            )
        ));
        $fields->description( __( 'Pause the scroll when the announcement is hovered.', 'announcer' ) );
        $fields->end();

        $fields->build();

    }

    public static function design_bar(){

        $settings = self::$settings;
        self::template_designer( 'style_bar', $settings[ 'style_bar' ], [ 'width' ] );

    }

    public static function design_primary_btn(){

        $settings = self::$settings;
        self::template_designer( 'style_primary_btn', $settings[ 'style_primary_btn' ], [ 'link_color', 'padding', 'background_image' ] );

    }

    public static function design_secondary_btn(){

        $settings = self::$settings;
        self::template_designer( 'style_secondary_btn', $settings[ 'style_secondary_btn' ], [ 'link_color', 'padding', 'background_image' ] );

    }

    public static function close_general(){

        $fields = new ANCR_Form_Builder();
        $settings = self::$settings;

        $fields->start();
        $fields->heading( __( 'Display close button', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'close_btn' ],
            'name' => 'settings[close_btn]',
            'list' => array(
                'yes' => __( 'Yes', 'announcer' ),
                'no' => __( 'No', 'announcer' )
            )
        ));
        $fields->end();

        $fields->start();
        $fields->heading( __( 'Automatically close after', 'announcer' ) );
        $fields->field( 'text', array(
            'value' => $settings[ 'auto_close' ],
            'type' => 'number',
            'name' => 'settings[auto_close]',
            'class' => 'small-text',
            'after_text' => 'seconds'
        ));
        $fields->end();

        $fields->start();
        $fields->heading( __( 'Close when a linked inside message is clicked', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'close_content_click' ],
            'name' => 'settings[close_content_click]',
            'list' => array(
                'yes' => __( 'Yes', 'announcer' ),
                'no' => __( 'No', 'announcer' )
            )
        ));
        $fields->end();

        $fields->start();
        $fields->heading( __( 'On close animation', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'close_animation' ],
            'name' => 'settings[close_animation]',
            'list' => array(
                'none' => __( 'None', 'announcer' ),
                'slide' => __( 'Slide', 'announcer' ),
                'fade' => __( 'Fade', 'announcer' )
            )
        ));
        $fields->end();

        $fields->build();

    }

    public static function close_keep_closed(){

        $fields = new ANCR_Form_Builder();
        $settings = self::$settings;

        $fields->start();
        $fields->heading( __( 'Keep closed', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'keep_closed' ],
            'name' => 'settings[keep_closed]',
            'list' => array(
                'no' => 'No',
                'yes' => 'Yes'
            )
        ));
        $fields->end();

        $fields->start('keep_closed_duration');
        $fields->heading( __( 'Keep closed for duration', 'announcer' ) );
        $fields->field( 'text', array(
            'value' => $settings[ 'closed_duration' ],
            'name' => 'settings[closed_duration]',
            'type' => 'number',
            'class' => 'small-text',
            'after_text' => __( 'days', 'announcer' ),
            'helper' => __( 'Enter the number of days the announcement has to be kept closed. Enter 0 for only the user session.', 'announcer' )
        ));
        $fields->end();

        $fields->build();

    }

    public static function location_rules(){

        $fields = new ANCR_Form_Builder();
        $settings = self::$settings;

        echo '<h4>' . __( 'Display conditions', 'announcer' ) . '</h4>';
        ANCR_Admin::$location_rules->display_rules('settings[location_rules]', $settings[ 'location_rules' ]);

        $fields->start();
        $fields->heading( __( 'Show on devices', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $settings[ 'devices' ],
            'name' => 'settings[devices]',
            'list' => array(
                'all' => __( 'On both desktop and mobile devices', 'announcer' ),
                'mobile_only' => __( 'On mobile devices alone', 'announcer' ),
                'desktop_only' => __( 'On desktops alone', 'announcer' )
            )
        ));
        $fields->end();

        $fields->build();

        echo '<a class="pro_note" href="https://www.aakashweb.com/wordpress-plugins/announcer/?utm_source=admin&utm_medium=visitor_conditions&utm_campaign=ancr-pro#pro" target="_blank"><span class="dashicons dashicons-visibility"></span> Want to target users by referrer, query parameters, login status, user role, user views, user login count etc. ? Use <em>visitor conditions</em> feature and build your rules.</a>';

    }

    public static function template_designer( $field_name = false, $values = [], $hide = [] ){

        if( !$field_name ){
            return;
        }

        $fields = new ANCR_Form_Builder();
        $defaults = ANCR_Settings::template_defaults( 'designer', $field_name );
        $values = wp_parse_args( $values, $defaults );

        $fields->start('gradient_support_wrap');
        $fields->heading( __( 'Background color', 'announcer' ) );
        $fields->field( 'text', [
            'name' => "settings[$field_name][background_color]",
            'class' => 'color_picker wide_input gradient_support',
            'value' => $values[ 'background_color' ],
            'helper' => __( 'To set gradient as background, generate the gradient <a href="https://cssgradient.io/" target="_blank">in this page</a> and paste the value here. Example value: <code>linear-gradient(45deg, red, blue)</code> without semicolon', 'announcer' )
        ]);
        $fields->end();

        $fields->start();
        $fields->heading( __( 'Font', 'announcer' ) );
        $fields->field( 'text', [
            'name' => "settings[$field_name][font_color]",
            'class' => 'color_picker',
            'value' => $values[ 'font_color' ],
            'before_text' => __( 'Color', 'announcer' )
        ]);
        $fields->field( 'text', [
            'name' => "settings[$field_name][font_size]",
            'type' => 'number',
            'class' => 'small-text',
            'value' => $values[ 'font_size' ],
            'before_text' => __( 'Size', 'announcer' ),
            'after_text' => __( 'px' )
        ]);

        if( !in_array( 'link_color', $hide ) ){
            $fields->field( 'text', [
                'name' => "settings[$field_name][link_color]",
                'class' => 'color_picker',
                'value' => $values[ 'link_color' ],
                'before_text' => __( 'Link color', 'announcer' )
            ]);
        }

        $fields->description( __( 'Leave empty to take default theme settings.', 'announcer' ) );
        $fields->end();

        $fields->start();
        $fields->heading( __( 'Border', 'announcer' ) );
        $fields->field( 'text', [
            'type' => 'number',
            'name' => "settings[$field_name][border_width]",
            'class' => 'small-text',
            'before_text' => __( 'Width', 'announcer' ),
            'after_text' => 'px',
            'value' => $values[ 'border_width' ]
        ]);
        $fields->field( 'select', [
            'name' => "settings[$field_name][border_style]",
            'list' => [
                'solid' => 'Solid',
                'dashed' => 'Dashed'
            ],
            'before_text' => __( 'Style', 'announcer' ),
            'value' => $values[ 'border_style' ]
        ]);
        $fields->field( 'text', [
            'name' => "settings[$field_name][border_color]",
            'class' => 'color_picker',
            'before_text' => __( 'Color', 'announcer' ),
            'value' => $values[ 'border_color' ]
        ]);
        $fields->field( 'text', [
            'name' => "settings[$field_name][border_radius]",
            'type' => 'number',
            'class' => 'small-text',
            'before_text' => __( 'Rounded corner radius', 'announcer' ),
            'after_text' => 'px',
            'value' => $values[ 'border_radius' ]
        ]);
        $fields->description( __( 'Leave empty to take default theme settings.', 'announcer' ) );
        $fields->end();

        if( !in_array( 'width', $hide ) ){
            $fields->start();
            $fields->heading( __( 'Width', 'announcer' ) );
            $fields->field( 'text', [
                'name' => "settings[$field_name][width]",
                'value' => $values[ 'width' ]
            ]);
            $fields->description( __( 'The width of the item. Include units like px, %. Example: 200px. Leave empty to take auto width.', 'announcer' ) );
            $fields->end();
        }

        if( !in_array( 'padding', $hide ) ){
            $fields->start();
            $fields->heading( __( 'Padding', 'announcer' ) );
            $fields->field( 'text', [
                'name' => "settings[$field_name][padding]",
                'type' => 'number',
                'value' => $values[ 'padding' ],
                'class' => 'small-text',
                'after_text' => 'px'
            ]);
            $fields->description( __( 'Extra padding for the announcement bar if required. Leave blank to use default.', 'announcer' ) );
            $fields->end();
        }

        $fields->start();
        $fields->heading( __( 'Shadow', 'announcer' ) );
        $fields->field( 'select', array(
            'value' => $values[ 'shadow' ],
            'name' => "settings[$field_name][shadow]",
            'list' => array(
                'yes' => __( 'Yes', 'yes' ),
                'no' => __( 'No', 'no' )
            )
        ));
        $fields->end();

        if( !in_array( 'background_image', $hide ) ){
            $fields->start();
            $fields->heading( __( 'Background image', 'announcer' ) );
            $fields->field( 'text', [
                'name' => "settings[$field_name][background_image]",
                'value' => $values[ 'background_image' ],
                'before_text' => __( 'Image URL', 'announcer' ),
                'placeholder' => 'https://example.com/image.png'
            ]);

            $fields->field( 'select', array(
                'value' => $values[ 'background_size' ],
                'name' => "settings[$field_name][background_size]",
                'before_text' => __( 'Size', 'announcer' ),
                'list' => array(
                    'cover' => 'Full width',
                    'auto' => 'Tile'
                )
            ));
            $fields->description( sprintf( __( 'A background image URL starting with <code>http</code>. Upload it to your <a href="%s" target="_blank">library</a>, copy image URL or enter any other image URL', 'announcer' ), admin_url( 'upload.php' ) ) );
            $fields->end();
        }

        echo '<div class="designer_form">';
        $fields->build();
        echo '</div>';

    }

    public static function template_cta_buttons( $button = [] ){

        $defaults = ANCR_Settings::template_defaults( 'cta_buttons' );
        $button = wp_parse_args( $button, $defaults );

        echo '<div class="cta_btn_item">';

        echo '<div class="cta_common cta_fields">';

        echo '<span>' . ANCR_Fields::field( 'text', [
            'name' => 'settings[cta_buttons][text][]',
            'value' => $button[ 'text' ],
            'class' => 'widefat',
            'before_text' => __( 'Button text', 'announcer' )
        ]) . '</span>';

        echo '<span>' . ANCR_Fields::field( 'select', [
            'name' => 'settings[cta_buttons][type][]',
            'value' => $button[ 'type' ],
            'list' => [
                'primary' => __( 'Primary button', 'announcer' ),
                'secondary' => __( 'Secondary button', 'announcer' )
            ],
            'class' => 'widefat',
            'before_text' => __( 'Button type', 'announcer' )
        ]) . '</span>';

        echo '<span>' . ANCR_Fields::field( 'select', [
            'name' => 'settings[cta_buttons][on_click][]',
            'value' => $button[ 'on_click' ],
            'class' => 'widefat cta_on_click',
            'list' => [
                'open_link' => __( 'Open link', 'announcer' ),
                'close_bar' => __( 'Close announcement', 'announcer' )
            ],
            'before_text' => __( 'On click', 'announcer' )
        ]) . '</span>';

        echo '<span>';
        echo ANCR_Fields::field( 'text', [
            'name' => 'settings[cta_buttons][title][]',
            'value' => $button[ 'title' ],
            'class' => 'widefat',
            'before_text' => __( 'Hover text', 'announcer' )
        ]);
        echo '</span>';

        echo '</div>'; //.cta_common

        echo '<div class="cta_prop_wrap">';

        echo '<div class="cta_prop cta_open_link_prop cta_fields">';
        echo '<span>';
        echo ANCR_Fields::field( 'text', [
            'name' => 'settings[cta_buttons][link_url][]',
            'type' => 'text',
            'value' => $button[ 'link_url' ],
            'placeholder' => 'Enter URL',
            'class' => 'widefat',
            'before_text' => __( 'Link URL', 'announcer' )
        ]);
        echo '</span>';

        echo '<span>';
        echo ANCR_Fields::field( 'select', [
            'name' => 'settings[cta_buttons][link_target][]',
            'value' => $button[ 'link_target' ],
            'list' => [
                'new_window' => __( 'Open in new window', 'announcer' ),
                'same_window' => __( 'Open in same window', 'announcer' )
            ],
            'class' => 'widefat',
            'before_text' => __( 'Open link in', 'announcer' )
        ]);
        echo '</span>';

        echo '<span>';
        echo ANCR_Fields::field( 'select', [
            'name' => 'settings[cta_buttons][no_follow][]',
            'value' => $button[ 'no_follow' ],
            'list' => [
                'no' => __( 'No', 'announcer' ),
                'yes' => __( 'Yes', 'announcer' )
            ],
            'class' => 'widefat',
            'before_text' => __( 'Add "nofollow" attribute', 'announcer' )
        ]);
        echo '</span>';

        echo '<span>';
        echo ANCR_Fields::field( 'select', [
            'name' => 'settings[cta_buttons][link_do_close][]',
            'value' => $button[ 'link_do_close' ],
            'list' => [
                'no' => __( 'No', 'announcer' ),
                'yes' => __( 'Yes', 'announcer' )
            ],
            'class' => 'widefat',
            'before_text' => __( 'Close announcement on click', 'announcer' )
        ]);
        echo '</span>';

        echo '</div>';

        echo '</div>'; // .cta_prop

        echo '<a href="#" class="cta_delete" title="' . __( 'Delete button', 'announcer' ) . '"><span class="dashicons dashicons-no"></span></a>';

        echo '</div>';//.cta_btn_item

    }

    public static function pro_tab(){

        $foot = '<p class="pro_ctas"><a href="https://www.aakashweb.com/wordpress-plugins/announcer/?utm_source=admin&utm_medium=tab-get&utm_campaign=ancr-pro#pro" class="button button-primary" target="_blank">Upgrade to PRO version</a> <a href="https://www.aakashweb.com/wordpress-plugins/announcer/?utm_source=admin&utm_medium=tab-info&utm_campaign=ancr-pro#pro" class="button" target="_blank">More information</a></p>';

        echo '<div class="pro_intro"><b>Announcer PRO</b> version includes all the features of free version along with below features to help you fine tune and spice up your announcements</div>';

        echo $foot;

        echo '<ul class="pro_list">';
        echo '<li><span class="dashicons dashicons-image-rotate"></span> <h4>Multiple Messages in one –</h4><p>You can add multiple messages to one announcement bar and add a "ticker" animation to scroll through them one by one.</p>
        <ul class="vc_list">
            <li>Arrow buttons to click next/previous announcement</li>
            <li>Autoplay</li>
            <li>Randomize order</li>
            <li>Display one announcement in random</li>
        </ul><br/>
        <p><a href="https://www.aakashweb.com/wordpress-plugins/announcer/?utm_source=admin&utm_medium=multi&utm_campaign=ancr-pro#pro" target="_blank"><img src="' . ANCR_ADMIN_URL . 'images/multi.gif" class="pro_img" width="60%" /></a></p>
        </li>';

        echo '<li><span class="dashicons dashicons-clock"></span> <h4>Countdown Timer –</h4><p>Display a countdown timer next to your announcements to notify any deadline or to increase engagement. You can customize the countdown timer as you want. Multiple modes are available out of the box.</p><br/>
        <p><a href="https://www.aakashweb.com/wordpress-plugins/announcer/?utm_source=admin&utm_medium=countdown&utm_campaign=ancr-pro#pro" target="_blank"><img src="' . ANCR_ADMIN_URL . 'images/countdown.png" class="pro_img" width="70%" /></a></p>
        </li>';

        echo '<li><span class="dashicons dashicons-admin-page"></span> <h4>Duplicate Announcement –</h4><p>You can easily duplicate announcements at the click of a button.</p><br/>
        <p><a href="https://www.aakashweb.com/wordpress-plugins/announcer/?utm_source=admin&utm_medium=duplicate&utm_campaign=ancr-pro#pro" target="_blank"><img src="' . ANCR_ADMIN_URL . 'images/duplicate-announcement.png" class="pro_img" width="50%" /></a></p>
        </li>';

        echo '<li><span class="dashicons dashicons-visibility"></span> <h4>Visitor Conditions –</h4><p>With visitor conditions feature you can target visitors based on conditions like </p>
        <ul class="vc_list">
            <li>cookie</li>
            <li>referrer</li>
            <li>browser</li>
            <li>OS</li>
            <li>device type</li>
            <li>user login status</li>
            <li>user role</li>
            <li>no. of times user has logged in</li>
            <li>no. of times user has visited site</li>
            <li>text present in the URL</li>
            <li>custom function</li>
        </ul></li>';

        echo '<li><span class="dashicons dashicons-buddicons-groups"></span> <h4>Animation –</h4><p>Want to grab the attention of your announcements ? With announcer PRO you can add animation to your CTA buttons and funky transitions to the bar when they open/close</p></li>';

        echo '<li><span class="dashicons dashicons-shortcode"></span> <h4>Shortcode –</h4><p>With PRO version you can insert your announcement anywhere on your website using Shortcode like post/pages or even in theme templates directly using <code>[announcer]</code>.</p></li>';
        echo '</ul>';

        echo $foot;

    }

}

ANCR_Settings_Form::init();

?>