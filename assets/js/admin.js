(function($) {
    "use strict";
    $(document).ready(function() {

        //shortcode show hide
        var lmSelector='#timify_settings .lm_display_method .regular';
        var rtSelector='#timify_settings .rt_display_method .regular';
        var lmDSelected=$(lmSelector).val();
        var rtDSelected=$(rtSelector).val();

        $('#timify_settings .rt_shortcode_content').hide();
        $('#timify_settings .lm_shortcode_content').hide();
        $('#timify_settings .lm_post_date_selector').hide();
        $('#timify_settings .rt_post_meta_selector').hide();
        $('#timify_settings .lm_post_meta_selector').hide();
        
        if(rtDSelected=='shortcode_content'){
            $('#timify_settings .rt_'+rtDSelected).show();
        }
        if(rtDSelected=='inside_post_meta'){
            $('#timify_settings .rt_post_meta_selector').show();
        }
        if(lmDSelected=='shortcode_content'){
            $('#timify_settings .lm_'+lmDSelected).show();
        }
        if(lmDSelected=='replace_original'){
            $('#timify_settings .lm_post_date_selector').show();
        }
        if(lmDSelected=='inside_post_meta'){
            $('#timify_settings .lm_post_meta_selector').show();
        }
  
        $(lmSelector).on('change', function() {
            var lmSelectedVal=$(this).find('option:selected').val();
            if(lmSelectedVal=='shortcode_content'){
                $('#timify_settings .lm_'+lmSelectedVal).show();
            }else{
                $('#timify_settings .lm_shortcode_content').hide();
            }
            if(lmSelectedVal=='replace_original'){
                $('#timify_settings .lm_post_date_selector').show();
            }else{
                $('#timify_settings .lm_post_date_selector').hide();
            }
            if(lmSelectedVal=='inside_post_meta'){
                $('#timify_settings .lm_post_meta_selector').show();
            }else{
                $('#timify_settings .lm_post_meta_selector').hide();
            }

        });
  
        $(rtSelector).on('change', function() {
            var rtSelectedVal=$(this).find('option:selected').val();
            if(rtSelectedVal=='shortcode_content'){
                $('#timify_settings .rt_'+rtSelectedVal).show();
            }else{
                $('#timify_settings .rt_shortcode_content').hide();
            }
            if(rtSelectedVal=='inside_post_meta'){
                $('#timify_settings .rt_post_meta_selector').show();
            }else{
                $('#timify_settings .rt_post_meta_selector').hide();
            }
        });

        //ajax for admin dashboard top notice
        $('body').on('click', '.timify-notice .notice-dismiss', function() {
            $.ajax( {
                url: admin_js.ajaxurl,
                method: "POST",
                data: {
                    action: 'timify_remove_notification'
                }
            });
        });

    });
})(jQuery);