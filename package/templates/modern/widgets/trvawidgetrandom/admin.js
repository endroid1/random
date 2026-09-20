(function($){

    $(document).on('change', '#f_options\\:content_type', function(){

        var content_type = $(this).val();
        var prediction_field = $('#f_options\\:prediction_field');
        var background_field = $('#f_options\\:background_field');

        if(!content_type){
            prediction_field.html('<option value=""></option>');
            background_field.html('<option value=""></option>');
            return;
        }

        // Показываем индикатор загрузки
        prediction_field.prop('disabled', true).addClass('loading');
        background_field.prop('disabled', true).addClass('loading');

        $.post(window.location.href, {
            content_type: content_type
        }, function(result){

            prediction_field.prop('disabled', false).removeClass('loading');
            background_field.prop('disabled', false).removeClass('loading');

            // Обновляем список полей для предсказаний
            var prediction_options = '<option value=""></option>';
            if(result.prediction_fields){
                $.each(result.prediction_fields, function(key, value){
                    prediction_options += '<option value="'+key+'">'+value+'</option>';
                });
            }
            prediction_field.html(prediction_options);

            // Обновляем список полей для фона
            var background_options = '<option value=""></option>';
            if(result.background_fields){
                $.each(result.background_fields, function(key, value){
                    background_options += '<option value="'+key+'">'+value+'</option>';
                });
            }
            background_field.html(background_options);

        }, 'json');

    });

})(jQuery);