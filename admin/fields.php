<?php

if( ! defined( 'ABSPATH' ) ) exit;

class ANCR_Fields{

    public static function field( $field_type, $params = [] ){

        $defaults = [
            'text' => [
                'type' => 'text',
                'value' => '',
                'id' => '',
                'class' => 'regular-text',
                'name' => '',
                'placeholder' => '',
                'required' => '',
                'autocomplete' => '',
                'helper' => '',
                'tooltip' => '',
                'before_text' => '',
                'after_text' => '',
                'custom' => ''
            ],

            'select' => [
                'id' => '',
                'class' => '',
                'name' => '',
                'list' => [],
                'value' => '',
                'helper' => '',
                'tooltip' => '',
                'custom' => ''
            ],

            'textarea' => [
                'type' => 'text',
                'value' => '',
                'name' => '',
                'id' => '',
                'class' => '',
                'placeholder' => '',
                'rows' => '',
                'cols' => '',
                'helper' => '',
                'tooltip' => '',
                'custom' => ''
            ],

            'image_select' => [
                'id' => '',
                'class' => '',
                'name' => '',
                'list' => [],
                'value' => '',
                'helper' => '',
                'tooltip' => '',
                'custom' => ''
            ]

        ];

        $params = wp_parse_args( $params, $defaults[ $field_type ] );
        $params = self::clean_attr( $params );
        $field_html = '';

        extract( $params, EXTR_SKIP );

        $id_attr = empty( $id ) ? '' : 'id="' . $id . '"';
        $class_attr = empty( $class ) ? '' : 'class="' . $class . '"';

        if( !empty( $before_text ) ){
            $field_html .= "<span class='field_before_text'>$before_text</span>";
        }

        switch( $field_type ){
            case 'text':
                $auto_complete_attr = empty( $autocomplete ) ? '' : 'autocomplete="' . $autocomplete . '"';
                $field_html .= "<input type='$type' $class_attr $id_attr name='$name' value='" . esc_attr( $value ) . "' placeholder='$placeholder' " . ( $required ? "required='$required'" : "" ) . "  $custom $auto_complete_attr />";
            break;

            case 'select':
                $field_html .= "<select name='$name' $class_attr $id_attr $custom>";
                foreach( $list as $k => $v ){
                    $field_html .= "<option value='$k' " . selected( $value, $k, false ) . ">$v</option>";
                }
                $field_html .= "</select>";
            break;

            case 'textarea':
                $field_html .= "<textarea name='$name' $class_attr $id_attr placeholder='$placeholder' rows='$rows' cols='$cols' $custom>$value</textarea>";
            break;

            case 'image_select':
                $field_html .= "<ul class='ancr_image_select' data-selected='$value'>";
                foreach( $list as $k => $v ){
                    $field_html .= "<li data-value='$k'><img src='" . ANCR_ADMIN_URL . "/images/" . $v[1] . "' /><span>" . wp_kses_post( $v[0] ) . "</span></li>";
                }
                $field_html .= "</ul>";
                $field_html .= "<input type='hidden' name='$name' value='$value' />";
            break;

        }

        if( !empty( $tooltip ) ){
            $field_html .= "<div class='ancr_tt'><span class='dashicons dashicons-editor-help'></span><span class='ancr_tt_text'>$tooltip</span></div>";
        }

        if( !empty( $after_text ) ){
            $field_html .= "<span class='field_after_text'>$after_text</span>";
        }

        if( !empty( $helper ) ){
            $field_html .= "<p class='ancr_desc'>$helper</p>";
        }

        return $field_html;

    }

    public static function clean_attr( $a ){
        
        foreach( $a as $k=>$v ){
            if( is_array( $v ) ){
                $a[ $k ] = self::clean_attr( $v );
            }else{
                
                if( in_array( $k, array( 'custom', 'tooltip', 'helper', 'before_text', 'after_text' ) ) ){
                    $a[ $k ] = wp_kses_post( $v );
                }else{
                    $a[ $k ] = esc_attr( $v );
                }
            }
        }
        
        return $a;
    }

}


class ANCR_Form_Builder{

    public $fields = [];
    public $current_field = [];

    function start( $class = '', $attr = '' ){
        $this->current_field[ 'wrap_class' ] = $class;
        $this->current_field[ 'wrap_attr' ] = $attr;
    }

    function heading( $name ){
        $this->current_field[ 'heading' ] = $name;
    }

    function field( $type, $prop ){
        $new_field = [
            'field_type' => $type,
            'field_prop' => $prop
        ];

        if( !isset( $this->current_field[ 'fields' ] ) ){
            $this->current_field[ 'fields' ] = [];
        }

        array_push( $this->current_field[ 'fields' ], $new_field );
    }

    function description( $desc ){
        $this->current_field[ 'description' ] = $desc;
    }

    function end(){
        array_push( $this->fields, $this->current_field );
        $this->current_field = [];
    }

    function build(){
        foreach( $this->fields as $field_wrap ){
            $class = empty( $field_wrap[ 'wrap_class' ] ) ? 'class="field_wrap"' : ' class="field_wrap ' . esc_attr( $field_wrap[ 'wrap_class' ] ) . '" ';
            $attr = empty( $field_wrap[ 'wrap_attr' ] ) ? '' : $field_wrap[ 'wrap_attr' ];

            echo '<div ' . $class . ' ' . $attr . '>';
            echo '<label class="field_label">' . esc_html( $field_wrap[ 'heading' ] ) . '</label>';
            echo '<div>';
            echo '<div class="field_val_wrap">';
            if( isset( $field_wrap[ 'fields' ] ) ){
                foreach( $field_wrap[ 'fields' ] as $field ){
                    echo '<div class="field_val">';
                    if( $field[ 'field_type' ] == 'html' ){
                        echo $field[ 'field_prop' ];
                    }else{
                        echo ANCR_Fields::field( $field[ 'field_type' ], $field[ 'field_prop' ] );
                    }
                    echo '</div>';
                }
            }
            echo '</div>';

            if( isset( $field_wrap[ 'description' ] ) ){
                echo '<p class="ancr_desc">' . wp_kses_post( $field_wrap[ 'description' ] ) . '</p>';
            }

            echo '</div>';
            echo '</div>';
        }

        $this->fields = [];
    }

}

?>