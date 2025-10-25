(function($){

    $(document).on('change', '#f_ctype_name', function(){

        var content_type = $(this).val();
        var text_field = $('#f_text_field');
        var image_field = $('#f_image_field');
        var title_field = $('#f_title_field');

        if(!content_type){
            text_field.html('<option value=""></option>');
            image_field.html('<option value=""></option>');
            title_field.html('<option value=""></option>');
            return;
        }

        text_field.prop('disabled', true).addClass('loading');
        image_field.prop('disabled', true).addClass('loading');
        title_field.prop('disabled', true).addClass('loading');

        $.post(window.location.href, {
            content_type: content_type
        }, function(result){

            text_field.prop('disabled', false).removeClass('loading');
            image_field.prop('disabled', false).removeClass('loading');
            title_field.prop('disabled', false).removeClass('loading');

            // Текстовые поля
            var text_options = '<option value=""></option>';
            if(result.text_fields){
                $.each(result.text_fields, function(key, value){
                    text_options += '<option value="'+key+'">'+value+'</option>';
                });
            }
            text_field.html(text_options);

            // Поля изображений
            var image_options = '<option value=""></option>';
            if(result.image_fields){
                $.each(result.image_fields, function(key, value){
                    image_options += '<option value="'+key+'">'+value+'</option>';
                });
            }
            image_field.html(image_options);

            // Поля заголовков
            var title_options = '<option value=""></option>';
            if(result.text_fields){
                $.each(result.text_fields, function(key, value){
                    title_options += '<option value="'+key+'">'+value+'</option>';
                });
            }
            title_field.html(title_options);

        }, 'json');

    });

})(jQuery);