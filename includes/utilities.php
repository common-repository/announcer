<?php

if( ! defined( 'ABSPATH' ) ) exit;

class ANCR_Utilities{

    public static function style_generator( $styles ){

        $properties = [];
        extract( $styles, EXTR_SKIP );

        if( !empty( $background_color ) ){
            if( isset( $background_image ) && !empty( $background_image ) ){
                $properties[ 'background-color' ] = $background_color;
            }else{
                $properties[ 'background' ] = $background_color;
            }
        }

        if( !empty( $font_color ) ){
            $properties[ 'color' ] = $font_color . ' !important';
        }

        if( !empty( $font_size ) ){
            $properties[ 'font-size' ] = "{$font_size}px !important";
        }

        if( isset( $border_width ) && $border_width > 0 ){
            $border_style = empty( $border_style ) ? 'solid' : $border_style;
            $border_color = empty( $border_color ) ? 'transparent' : $border_color;
            $properties[ 'border' ] = "{$border_width}px $border_style $border_color";
        }

        if( isset( $border_radius ) && $border_radius > 0 ){
            $properties[ 'border-radius' ] = "{$border_radius}px";
        }

        if( !empty( $width ) ){
            $properties[ 'min-width' ] = $width;
        }

        if( isset( $shadow ) && $shadow == 'yes' ){
            $properties[ 'box-shadow' ] = '0 2px 4px -2px rgba(0, 0, 0, 0.5)';
        }

        if( isset( $background_image ) && !empty( $background_image ) ){
            $properties[ 'background-image' ] = "url('$background_image')";
            if( isset( $background_size ) && !empty( $background_size ) ){
                $properties[ 'background-size' ] = $background_size;
            }
        }

        $props_text = '';
        foreach( $properties as $prop => $value ){
            $value = trim( str_replace( [ ';', '{', '}', '@' ], '', $value ) );
            if( empty( $value ) || strtolower( $value ) == 'px' ){
                continue;
            }
            $props_text .= "{$prop}:{$value};";
        }

        return $props_text;

    }

    public static function datetime_timestamp( $datetime, $timezone ){

        if( empty( $datetime ) ){
            return '';
        }

        try{
            $tz = new DateTimezone( $timezone );
        }catch( Exception $e ) {
            $tz = null;
        }

        try{
            $t = new DateTime( $datetime, $tz );
            return $t->getTimestamp();
        }catch( Exception $e ){
            return '';
        }

    }

    public static function build_attrs( $attrs ){

        $attrs_pair = [];
        foreach( $attrs as $name => $value ){
            $value = trim( $value );
            if( empty( $value ) ){
                continue;
            }
            $value = ( $name == 'href' ) ? esc_url( $value ) : esc_attr( $value );
            $attrs_string = esc_attr( $name ) . '="' . $value . '"';
            array_push( $attrs_pair, $attrs_string );
        }

        return implode( ' ', $attrs_pair );

    }

}

?>