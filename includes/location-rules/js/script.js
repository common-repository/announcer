(function($){
$(document).ready(function(){
    
    var init = function(){
        
        ancr_lr_sub_criteria();
        
        ancr_lr_generate_rules();
        
        ancr_lr_init_rules_box();
        
        $( '.ancr_lr_page' ).each( function(){
            ancr_lr_update_rule_helper( $(this) );
        });
        
    }
    
    var ancr_lr_get_props = function( $ele ){
        
        var id = $ele.closest( '.ancr_lr_rules_wrap' ).attr( 'data-id' );
        var global_props = window[ 'ancr_lr_' + id ];
        
        return {
            'id': id,
            'ajax_url': global_props[ 'ajax_url' ],
            'ajax_handler': global_props[ 'ajax_handler' ],
            'rules': global_props[ 'rules' ],
            'nonce': global_props[ 'nonce' ]
        };
    }
    
    var ancr_lr_generate_rules = function(){
    
        $( '.ancr_lr_rules_wrap' ).each(function(){
            
            tval = new Array();
            $wrap = $(this);
            $tinfo = $wrap.find( '.ancr_lr_rule_info' );
            $gadd = $wrap.find( '.ancr_lr_group_add' );
            $rule_box = $wrap.find( '.ancr_lr_rule_value' );
            $tgrp = $wrap.find( '.ancr_lr_rules_box .ancr_lr_group_wrap' );
            
            i = 0;
            $( $tgrp ).each(function(){
                $trle = $(this).find( '.ancr_lr_rule_wrap' );
                j = 0;
                tval[i] = new Array();
                $( $trle ).each(function(){
                    
                    tval[i][j] = [
                        $(this).find( '.ancr_lr_page' ).val(),
                        $(this).find( '.ancr_lr_operator' ).val(),
                        $(this).find( '.ancr_lr_value' ).val()
                    ];
                    
                    j++;
                });
                i++;
            });
            
            $rule_box.val( btoa( JSON.stringify( tval ) ) );
            
            if( $tgrp.length == 0 ){
                $tinfo.show();
                $gadd.text( 'Add new rules' );
            }else{
                $tinfo.hide();
                $gadd.text( ' Add another page ' );
            }
            
        });
    }
    
    var ancr_lr_sub_criteria = function(){
        
        $('.ancr_lr_group_wrap').each(function(){
            
            var props = ancr_lr_get_props( $(this) );
            $master_rule = $(this).find( '.ancr_lr_rule_wrap:first-child' );
            var master = $master_rule.find( '.ancr_lr_page' ).val();
            
            $( this ).find( '.ancr_lr_rule_wrap' ).each(function(){
                if( $(this).index() == 0 )
                    return true;
                $(this).find( '.ancr_lr_page option' ).each(function(){
                    if( $.inArray( $(this).val(), props.rules[ master ][ 'children' ] ) == -1 ){
                        $(this).remove();
                    }
                });
            });
            
            if( 'children' in props.rules[ master ] && props.rules[ master ][ 'children' ].length > 0 ){
                $master_rule.find( '.ancr_lr_rule_add' ).show()
            }else{
                $master_rule.find( '.ancr_lr_rule_add' ).hide()
            }
            
        });
        
        $( '.ancr_lr_page' ).each(function(){
            ancr_lr_update_rule_helper( $(this) );
        });
    }
    
    var ancr_lr_add_rule = function( group, btn ){
        grp_temp = $( '.ancr_lr_rules_temp' ).html();
        rule_temp = $( '.ancr_lr_rules_temp .ancr_lr_group_wrap').html();
        
        if( group ){
            btn.closest( '.ancr_lr_rules_wrap' ).find('.ancr_lr_rules_box').append( grp_temp );
        }else{
            btn.closest( '.ancr_lr_group_wrap' ).append( rule_temp );
        }
        
        ancr_lr_sub_criteria();
        ancr_lr_generate_rules();
    }

    var ancr_lr_remove_rule = function( btn ){
        $rule = btn.parent();
        $grp = $rule.parent();

        if( $rule.index() == 0 ){
            $grp.empty();
        }
        
        $rule.remove();
        
        if( $grp.children().length == 0 ){
            $grp.remove();
        }
        
        ancr_lr_generate_rules();
    }

    var ancr_lr_update_rule_helper = function( pageBtn ){
        
        helper = pageBtn.find( 'option:selected' ).attr( 'data-helper' );
        
        if( helper == 0 ){
            pageBtn.siblings( '.ancr_lr_operator, .ancr_lr_value' ).hide();
        }else{
            pageBtn.siblings( '.ancr_lr_operator, .ancr_lr_value' ).show();
        }
        
        placeholder = pageBtn.find( 'option:selected' ).attr( 'data-placeholder' );
        if( placeholder ){
            pageBtn.siblings( '.ancr_lr_value' ).attr( 'placeholder', placeholder );
        }
        
    }
    
    var ancr_lr_init_rules_box = function(){
        
        $( '.ancr_lr_rules_type' ).each(function(){
            
            var $rulesBox = $(this).closest( '.ancr_lr_rules_wrap' ).find( '.ancr_lr_rules_inner' );
            var $radio = $(this).find( '.ancr_lr_type:checked' );
            
            if( $radio.val().search('selected') != -1 ){
                $rulesBox.show();
            }else{
                $rulesBox.hide();
            }
            
        });
        
    }
    
    var ancr_lr_admin_tooltip = function( o ){
        
        if( !o.parent.is( document.ancr_lr_tt_parent ) ){
            ancr_lr_admin_tooltip_close();
        }else{
            return false;
        }
        
        $tt = $('<div class="ancr_lr_tooltip_wrap"><span class="dashicons dashicons-no-alt ancr_lr_tooltip_close" title="Close"></span><div class="ancr_lr_tooltip_cnt"></div></div>');
        
        $parent = o.parent;
        document.ancr_lr_tt_parent = $parent;
        
        if( o.class ) $tt.addClass( o.class );
        if( o.width ) $tt.width( o.width );
        if( o.height ) $tt.height( o.height );
        if( o.name ) $tt.attr( 'data-name', o.name );
        
        $tt.css({
            position: 'absolute',
            top: $parent.offset().top + $parent.outerHeight(),
            left: $parent.offset().left
        });
        
        $tt.appendTo( 'body' );
        
        if( typeof o.content == 'object' ){
            
            $tt.addClass( 'loading' );
            
            $.ajax(o.content).done(function(data){
                $tt.removeClass( 'loading' );
                $tt.find('.ancr_lr_tooltip_cnt').html( data );
                
                $footer = $tt.find( '.btn_settings_footer' );
                if( $footer.length > 0 ){
                    $footer.appendTo( '.ancr_lr_tooltip_wrap' );
                    $tt.find('.ancr_lr_tooltip_cnt').addClass( 'tt_has_footer' );
                }
                
                if( $.fn.wpColorPicker ){
                    $( '.wp-color' ).wpColorPicker();
                }
                
            });
            
        }else{
            
            $tt.find('.ancr_lr_tooltip_cnt').html( o.content );
            
        }
        
        if( o.class && o.class.search( 'ancr_lr_tooltip_popup' ) != -1 )
            $( 'body' ).addClass( 'hide_scrollbar' );
        
        // Positioning adjust
        winwid = $(window).width();
        ttwid = $tt.offset().left + $tt.outerWidth();
        
        if( winwid < ttwid  ){
            $tt.css( 'margin-left', -(ttwid+70-winwid));
        }
        
        $('.ancr_lr_tooltip_close').click(function(){
            ancr_lr_admin_tooltip_close();
        });
        
    }

    var ancr_lr_admin_tooltip_close = function(){
        $('.ancr_lr_tooltip_close').off( 'click' );
        $('.ancr_lr_tooltip_wrap').remove();
        $( 'body' ).removeClass( 'hide_scrollbar' );
        document.ancr_lr_tt_parent = false;
    }
    
    $(document).on( 'change', '.ancr_lr_rule_select', function(e){
        
        var props = ancr_lr_get_props( $(this) );
        
        $parent = $(this).parent();
        $parent.find( '.ancr_lr_rule_selector, .ancr_lr_btn_menu' ).remove();
        
        $.get( props.ajax_url, {
        
            action: props.ajax_handler,
            rule_id: $(this).val(),
            _ajax_nonce: props.nonce
            
        }).done(function( data ){
            
            $parent.append( '<span class="ancr_lr_rule_selector">' + data + '</span>' );
            
        });
        
    });
    
    $(document).on( 'click', '.ancr_lr_rules_menu', function(){
        $(this).siblings('.ancr_lr_rule_selector').fadeToggle('fast');
    });
    
    $(document).on( 'click', '.ancr_lr_rules_remove', function(){
        $(this).parent().remove();
    });
    
    $(document).on( 'click', '.add_ancr_lr_rule', function(e){
        e.preventDefault();
        
        rule = $('.ancr_lr_rules_temp').html();
        rule = rule.replace( '%rule_id%', $(this).attr( 'data-id' ) );
        $(this).siblings( '.ancr_lr_rules_list' ).append( '<li>' + rule + '</li>' );
        
    });
    
    $(document).on( 'click', '.ancr_lr_group_add', function(e){
        e.preventDefault();
        ancr_lr_add_rule( true, $(this) );
    });
    
    $(document).on( 'click', '.ancr_lr_rule_add', function(e){
        e.preventDefault();
        ancr_lr_add_rule( false, $(this) );
    });
    
    $(document).on( 'click', '.ancr_lr_rule_remove', function(e){
        e.preventDefault();
        ancr_lr_remove_rule( $(this) );
    });

    $(document).on( 'click', '.ancr_lr_value', function(e){
        
        var props = ancr_lr_get_props( $(this) );
        
        $list = $(this).siblings( '.ancr_lr_page' );
        val = $list.val();
        helper = $list.find( 'option:selected' ).attr( 'data-helper' );
        
        if( helper == "1" ){
            ancr_lr_admin_tooltip({
                parent: $(this),
                class: 'ancr_lr_rules_tt',
                height: '200px',
                content: {
                    url: props.ajax_url,
                    data: {
                        _ajax_nonce: props.nonce,
                        action: props.ajax_handler,
                        rule_id: val,
                        selected: $(this).val()
                    }
                }
            });
        }
    });
    
    $(document).on( 'change', '.ancr_lr_value', function(e){
        ancr_lr_generate_rules();
    });
    
    $(document).on( 'click', '.ancr_lr_rules_tt input[type="checkbox"]', function(e){
        var temp = [];
        $(this).closest( '.ancr_lr_rules_tt' ).find( 'input[type="checkbox"]' ).each(function(){
            if( $(this).is(':checked') )
                temp.push( $(this).val() );
        });
        document.ancr_lr_tt_parent.val( temp );
        document.ancr_lr_tt_parent.trigger( 'change' );
    });
    
    $(document).on( 'change', '.ancr_lr_page', function(e){
        ancr_lr_update_rule_helper( $(this) );
        ancr_lr_admin_tooltip_close();
        $(this).siblings( '.ancr_lr_value' ).val( '' );
        
        if( $(this).closest( '.ancr_lr_rule_wrap' ).index() == 0 ){
            $(this).closest( '.ancr_lr_group_wrap' ).children().not(':first-child' ).remove();
            ancr_lr_sub_criteria();
        }
        
        ancr_lr_generate_rules();
        
    });
    
    $(document).on( 'change', '.ancr_lr_rules_type .ancr_lr_type', function(e){
        e.preventDefault();
        
        var $rulesBox = $(this).closest( '.ancr_lr_rules_wrap' ).find( '.ancr_lr_rules_inner' );
        
        if( $(this).val().search('selected') != -1 ){
            $rulesBox.show();
        }else{
            $rulesBox.hide();
        }
        
    });
    
    init();
    
});
})( jQuery );