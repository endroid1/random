<?php 
$this->addCSS('templates/modern/widgets/trvawidgetrandom/css/random.css'); 

$backgroundStyle = '';
$backgroundImage = '';

// Функция для получения корректного полного пути к изображению
if (!function_exists('getRandomFullImagePath')) {
    function getRandomFullImagePath($path) {
        if (empty($path)) {
            return '';
        }
        
        $path = trim($path);
        
        // Абсолютные URL оставляем как есть
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0 || strpos($path, '//') === 0) {
            return $path;
        }
        
        // Пути относительно корня сайта
        if (strpos($path, '/') === 0) {
            return $path;
        }
        
        // Формируем путь через upload_host
        $upload_host = cmsConfig::get('upload_host');
        return rtrim($upload_host, '/') . '/' . ltrim($path, '/');
    }
}

// Определяем фоновое изображение
if (!empty($widget->options['enable_background_image'])) {
    // Для типа контента используем текущий фон из куки
    if ($widget->options['source'] == 'content' && !empty($widget->options['current_background'])) {
        $backgroundImage = getrandomFullImagePath($widget->options['current_background']);
    } 
    // Для ручного ввода используем заданное изображение
    elseif ($widget->options['source'] == 'manual' && !empty($widget->options['background_image'])) {
        $backgroundImage = html_image_src($widget->options['background_image'], 'original', true);
    }
    
    if ($backgroundImage) {
        $backgroundStyle = "
            background-size: {$widget->options['background_size']};
            background-position: {$widget->options['background_position']};
            background-repeat: {$widget->options['background_repeat']};
            background-image: url('{$backgroundImage}');
        ";
    }
}

$limit_interval_js = !empty($widget->options['limit_interval']) ? (int)$widget->options['limit_interval'] : 5;
$button_text_js = addslashes($widget->options['button_text']);
?>

<div id="random-widget-<?php echo $widget->id; ?>" 
     class="random-widget" 
     style="
         --title-color: <?php echo $widget->options['title_color']; ?>;
         --text-color: <?php echo $widget->options['text_color']; ?>;
         --button-color: <?php echo $widget->options['button_color']; ?>;
         --button-text-color: <?php echo $widget->options['button_text_color']; ?>;
         --background-opacity: <?php echo $widget->options['background_opacity'] / 100; ?>;
         --background-overlay: <?php echo $widget->options['background_overlay']; ?>;
         <?php echo $backgroundStyle; ?>
     ">
    
    <div class="random-overlay"></div>
    
    <div class="random-content">
    <?php if (!empty($widget->options['event_name'])): ?>
        <h2 class="random-title"><?php echo $widget->options['event_name']; ?></h2>
    <?php endif; ?>
        
        <div class="random-box" id="random-text-<?php echo $widget->id; ?>">
            <?php if (!empty($widget->options['current_prediction'])): ?>
                <?php echo $widget->options['current_prediction']; ?>
            <?php else: ?>
                <?php echo LANG_WD_TRVAWIDGETRANDOM_DEFAULT_PREDICTION; ?>
            <?php endif; ?>
        </div>
        
        <button class="random-button" 
                id="random-button-<?php echo $widget->id; ?>" 
                onclick="generaterandom<?php echo $widget->id; ?>()"
                <?php if (!$widget->options['is_button_enabled']): ?>disabled style="opacity: 0.6; cursor: not-allowed;"<?php endif; ?>>
            <?php echo $widget->options['button_text']; ?>
        </button>
    </div>
</div>

<script>
    const predictions<?php echo $widget->id; ?> = <?php echo json_encode($widget->options['predictions_array']); ?>;
    const widgetId<?php echo $widget->id; ?> = <?php echo $widget->id; ?>;
    const limitMode<?php echo $widget->id; ?> = '<?php echo $widget->options['limit_mode']; ?>';
    const limitInterval<?php echo $widget->id; ?> = <?php echo $limit_interval_js; ?>;
    const buttonText<?php echo $widget->id; ?> = '<?php echo $button_text_js; ?>';
    const sourceType<?php echo $widget->id; ?> = '<?php echo $widget->options['source']; ?>';
    const uploadHost<?php echo $widget->id; ?> = '<?php echo rtrim(cmsConfig::get("upload_host"), "/"); ?>';
    
    <?php if (!$widget->options['is_button_enabled']): ?>
        const nextAvailableTime<?php echo $widget->id; ?> = <?php echo $widget->options['next_available_time']; ?>;
        startCountdownTimer<?php echo $widget->id; ?>(nextAvailableTime<?php echo $widget->id; ?>);
    <?php endif; ?>

    function generaterandom<?php echo $widget->id; ?>() {
        const randomElement = document.getElementById('random-text-' + widgetId<?php echo $widget->id; ?>);
        const buttonElement = document.getElementById('random-button-' + widgetId<?php echo $widget->id; ?>);
        const widgetElement = document.getElementById('random-widget-' + widgetId<?php echo $widget->id; ?>);

        buttonElement.disabled = true;
        buttonElement.style.opacity = '0.6';
        buttonElement.style.cursor = 'not-allowed';
        buttonElement.textContent = '<?php echo LANG_WD_TRVAWIDGETRANDOM_LOADING; ?>';

        if(predictions<?php echo $widget->id; ?>.length === 0) {
            alert('<?php echo LANG_WD_TRVAWIDGETRANDOM_NO_PREDICTIONS; ?>');
            buttonElement.disabled = false;
            buttonElement.style.opacity = '1';
            buttonElement.style.cursor = 'pointer';
            buttonElement.textContent = buttonText<?php echo $widget->id; ?>;
            return;
        }
        
        const randomIndex = Math.floor(Math.random() * predictions<?php echo $widget->id; ?>.length);
        const predictionData = predictions<?php echo $widget->id; ?>[randomIndex];
        
        randomElement.style.opacity = '0';
        setTimeout(() => {
            // Устанавливаем текст предсказания
            randomElement.textContent = sourceType<?php echo $widget->id; ?> === 'content' ? 
                                        predictionData.text : 
                                        predictionData;
            randomElement.style.opacity = '1';

            // Обновляем фон если используется тип контента и есть изображение
            if(sourceType<?php echo $widget->id; ?> === 'content' && predictionData.background) {
                let bgPath = predictionData.background.trim();
                let fullImagePath = bgPath;
                
                if (!bgPath.startsWith('http://') && !bgPath.startsWith('https://') && !bgPath.startsWith('//')) {
                    if (bgPath.startsWith('/')) {
                        fullImagePath = bgPath;
                    } else {
                        fullImagePath = uploadHost<?php echo $widget->id; ?> + '/' + bgPath;
                    }
                }

                widgetElement.style.backgroundImage = "url('" + fullImagePath + "')";
                setBackgroundCookie<?php echo $widget->id; ?>(predictionData.background);
            }

            if(limitMode<?php echo $widget->id; ?> !== 'none') {
                setPredictionCookie<?php echo $widget->id; ?>(randomElement.textContent);
                
                const now = Math.floor(Date.now() / 1000);
                setrandomTimestamp<?php echo $widget->id; ?>(now);

                const nextAvailableTime = now + (limitInterval<?php echo $widget->id; ?> * 60);
                startCountdownTimer<?php echo $widget->id; ?>(nextAvailableTime);
            } else {
                buttonElement.disabled = false;
                buttonElement.style.opacity = '1';
                buttonElement.style.cursor = 'pointer';
                buttonElement.textContent = buttonText<?php echo $widget->id; ?>;
            }

        }, 300);
    }

    function setPredictionCookie<?php echo $widget->id; ?>(prediction) {
        const expiryDate = new Date();
        expiryDate.setDate(expiryDate.getDate() + 1);
        document.cookie = `random_${widgetId<?php echo $widget->id; ?>}_prediction=${encodeURIComponent(prediction)}; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;
    }

    function setBackgroundCookie<?php echo $widget->id; ?>(background) {
        const expiryDate = new Date();
        expiryDate.setDate(expiryDate.getDate() + 1);
        document.cookie = `random_${widgetId<?php echo $widget->id; ?>}_background=${encodeURIComponent(background)}; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;
    }

    function setrandomTimestamp<?php echo $widget->id; ?>(timestamp) {
        const expiryDate = new Date();
        expiryDate.setFullYear(expiryDate.getFullYear() + 1);
        document.cookie = `random_${widgetId<?php echo $widget->id; ?>}_time=${timestamp}; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;
    }

    function startCountdownTimer<?php echo $widget->id; ?>(targetTime) {
        const buttonElement = document.getElementById('random-button-' + widgetId<?php echo $widget->id; ?>);
        
        let timeLeftSeconds = targetTime - Math.floor(Date.now() / 1000);

        function updateTimer() {
            if (timeLeftSeconds <= 0) {
                buttonElement.disabled = false;
                buttonElement.style.opacity = '1';
                buttonElement.style.cursor = 'pointer';
                buttonElement.textContent = buttonText<?php echo $widget->id; ?>;
                
                const expiryDate = new Date();
                expiryDate.setDate(expiryDate.getDate() - 1);
                document.cookie = `random_${widgetId<?php echo $widget->id; ?>}_prediction=; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;
                document.cookie = `random_${widgetId<?php echo $widget->id; ?>}_background=; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;
                
                return;
            }

            const minutes = Math.ceil(timeLeftSeconds / 60);
            buttonElement.textContent = buttonText<?php echo $widget->id; ?> + '<?php echo sprintf(LANG_WD_TRVAWIDGETRANDOM_COUNTDOWN_TEXT, "' + minutes + '") ?>';

            timeLeftSeconds--;
            setTimeout(updateTimer, 1000);
        }

        updateTimer();
    }
</script>