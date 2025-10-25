<?php
$this->addCSS($this->getCSSFileName('random'));

$backgroundStyle = '';
$backgroundImage = '';

// Фон
if (!empty($options['enable_background_image'])) {
    if (!empty($options['current_image'])) {
        $backgroundImage = $options['current_image'];
    } elseif (!empty($options['background_image'])) {
        $backgroundImage = html_image_src($options['background_image'], 'original', true);
    }
    
    if ($backgroundImage) {
        $backgroundStyle = "
            background-size: {$options['background_size']};
            background-image: url('{$backgroundImage}');
        ";
    }
}
?>

<div id="random-widget-<?php echo $widget->id; ?>" 
     class="random-widget" 
     style="
         --title-color: <?php echo $options['title_color']; ?>;
         --text-color: <?php echo $options['text_color']; ?>;
         --button-color: <?php echo $options['button_color']; ?>;
         --button-text-color: <?php echo $options['button_text_color']; ?>;
         --background-opacity: <?php echo $options['background_opacity'] / 100; ?>;
         --background-overlay: <?php echo $options['background_overlay']; ?>;
         <?php echo $backgroundStyle; ?>
     ">
    
    <div class="random-overlay"></div>
    
    <div class="random-content">
        <?php if (!empty($options['event_name'])): ?>
            <h2 class="random-title"><?php echo $options['event_name']; ?></h2>
        <?php endif; ?>
        
        <div class="random-box" id="random-text-<?php echo $widget->id; ?>">
            <?php if (!empty($options['current_item'])): ?>
                <?php echo $options['current_item']; ?>
            <?php else: ?>
                Нажмите кнопку, чтобы получить случайный элемент
            <?php endif; ?>
        </div>
        
        <button class="random-button" 
                id="random-button-<?php echo $widget->id; ?>" 
                onclick="generateRandom<?php echo $widget->id; ?>()"
                <?php if (!$options['is_button_enabled']): ?>disabled style="opacity: 0.6; cursor: not-allowed;"<?php endif; ?>>
            <?php echo $options['button_text']; ?>
        </button>
    </div>
</div>

<script>
    const randomItems<?php echo $widget->id; ?> = <?php echo json_encode($options['items']); ?>;
    const widgetId<?php echo $widget->id; ?> = <?php echo $widget->id; ?>;
    const limitMode<?php echo $widget->id; ?> = '<?php echo $options['limit_mode']; ?>';
    const limitInterval<?php echo $widget->id; ?> = <?php echo !empty($options['limit_interval']) ? (int)$options['limit_interval'] : 5; ?>;
    const buttonText<?php echo $widget->id; ?> = '<?php echo addslashes($options['button_text']); ?>';
    
    <?php if (!$options['is_button_enabled']): ?>
        const nextAvailableTime<?php echo $widget->id; ?> = <?php echo $options['next_available_time']; ?>;
        startCountdownTimer<?php echo $widget->id; ?>(nextAvailableTime<?php echo $widget->id; ?>);
    <?php endif; ?>

    function generateRandom<?php echo $widget->id; ?>() {
        const textElement = document.getElementById('random-text-' + widgetId<?php echo $widget->id; ?>);
        const buttonElement = document.getElementById('random-button-' + widgetId<?php echo $widget->id; ?>);
        const widgetElement = document.getElementById('random-widget-' + widgetId<?php echo $widget->id; ?>);

        buttonElement.disabled = true;
        buttonElement.style.opacity = '0.6';
        buttonElement.style.cursor = 'not-allowed';
        buttonElement.textContent = 'Загрузка...';

        if(randomItems<?php echo $widget->id; ?>.length === 0) {
            alert('Нет доступных элементов');
            buttonElement.disabled = false;
            buttonElement.style.opacity = '1';
            buttonElement.style.cursor = 'pointer';
            buttonElement.textContent = buttonText<?php echo $widget->id; ?>;
            return;
        }
        
        const randomIndex = Math.floor(Math.random() * randomItems<?php echo $widget->id; ?>.length);
        const item = randomItems<?php echo $widget->id; ?>[randomIndex];
        
        textElement.style.opacity = '0';
        setTimeout(() => {
            textElement.textContent = item.text;
            textElement.style.opacity = '1';

            // Обновляем фон если есть изображение
            if(item.image) {
                widgetElement.style.backgroundImage = "url('" + item.image + "')";
                setImageCookie<?php echo $widget->id; ?>(item.image);
            }

            if(limitMode<?php echo $widget->id; ?> !== 'none') {
                setItemCookie<?php echo $widget->id; ?>(item.text);
                
                const now = Math.floor(Date.now() / 1000);
                setRandomTimestamp<?php echo $widget->id; ?>(now);

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

    function setItemCookie<?php echo $widget->id; ?>(text) {
        const expiryDate = new Date();
        expiryDate.setDate(expiryDate.getDate() + 1);
        document.cookie = `random_${widgetId<?php echo $widget->id; ?>}_item=${encodeURIComponent(text)}; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;
    }

    function setImageCookie<?php echo $widget->id; ?>(image) {
        const expiryDate = new Date();
        expiryDate.setDate(expiryDate.getDate() + 1);
        document.cookie = `random_${widgetId<?php echo $widget->id; ?>}_image=${encodeURIComponent(image)}; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;
    }

    function setRandomTimestamp<?php echo $widget->id; ?>(timestamp) {
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
                document.cookie = `random_${widgetId<?php echo $widget->id; ?>}_item=; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;
                document.cookie = `random_${widgetId<?php echo $widget->id; ?>}_image=; expires=${expiryDate.toUTCString()}; path=/; samesite=lax`;
                
                return;
            }

            const minutes = Math.ceil(timeLeftSeconds / 60);
            buttonElement.textContent = buttonText<?php echo $widget->id; ?> + ' доступно через ' + minutes + ' мин. Попробуйте позже';

            timeLeftSeconds--;
            setTimeout(updateTimer, 1000);
        }

        updateTimer();
    }
</script>