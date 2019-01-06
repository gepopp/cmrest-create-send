jQuery(document).ready(function ($) {

    var file_frame;
    $('.CMREST_upload_logo_button').on('click', function (event) {
        event.preventDefault();
        if (file_frame) {
            file_frame.open();
            return;
        }
        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            multiple: false	// Set to true to allow multiple files to be selected
        });
        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            // Do something with attachment.id and/or attachment.url here
            $('#image-preview').attr('src', attachment.url).css('width', 'auto').show();
            $('#image_attachment_id').val(attachment.id);
            // Restore the main post ID
            //wp.media.model.settings.post.id = wp_media_post_id;
        });
        // Finally, open the modal
        file_frame.open();
    });

    $('.CMREST-color-picker').wpColorPicker();

    $('.CMREST_call').live('click', function (event) {

        event.preventDefault();

        $('.CMREST_feedback').text('');
        $('.CMREST_countdown').text('');

        jQuery('.CMREST_loader_overlay').fadeIn().css('display', 'flex');

        tinyMCE.triggerSave();

        var call_data = $(this).closest('.CMREST_campaign_fields').serializeArray();
        var call = $(this).data('call');

        $.post(
            ajaxurl,
            {
                action: "CSREST_ajax_request",
                call: call,
                data: call_data
            },
            function (rsp) {
                $('.CMREST_feedback').text(rsp);
                refresh_metabox();
            }
        );

    });// API call ajax

    function refresh_metabox() {

        var count = 10;

        var countdown = setInterval(function () {

            $('.CMREST_countdown').text(count);
            count--;

            if (count == 2) {

                $.post(
                    ajaxurl,
                    {
                        action: "CMREST_reload_metabox"
                    },
                    function (rsp) {
                        $('#CMREST_Metabox_outer .inside').html(rsp);


                        if (rsp.indexOf('CMREST_email_text_input') !== -1) {

                            tinymce.execCommand('mceRemoveEditor', true, 'CMREST_email_text_input');
                            // init editor for newly appended div
                            var init = tinymce.extend({}, tinyMCEPreInit.mceInit['CMREST_email_text_input']);
                            try {
                                tinymce.init(init);
                            } catch (e) {
                            }

                            $.post(
                                ajaxurl,
                                {
                                    action: "CMREST_get_excerpt"
                                },
                                function (rsp) {
                                    tinymce.get('CMREST_email_text_input').setContent(rsp);
                                }
                            );
                        }

                    }
                );
            }


            if (count < 1) {
                clearInterval(countdown);
                jQuery('.CMREST_loader_overlay').fadeOut();

            }

        }, 1000);

    }

    function mceReInit() {

        tinymce.execCommand('mceRemoveEditor', true, id);
        var init = tinymce.extend({}, tinyMCEPreInit.mceInit[id]);
        try {
            tinymce.init(init);
        } catch (e) {
        }

        $('textarea[id="' + id + '"]').closest('form').find('input[type="submit"]').click(function () {
            if (getUserSetting('editor') == 'tmce') {
                var id = mce.find('textarea').attr('id');
                tinymce.execCommand('mceRemoveEditor', false, id);
                tinymce.execCommand('mceAddEditor', false, id);
            }
            return true;
        });


    }


});//ready
