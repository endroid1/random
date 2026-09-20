<?php
class widgettrvawidgetrandom extends cmsWidget {
    public $is_cacheable = false;
    
    public function run(){
        
        $this->options['predictions_array'] = array();
        $this->options['is_button_enabled'] = true;
        $this->options['next_available_time'] = 0;
        $this->options['time_left_seconds'] = 0;
        $this->options['current_prediction'] = '';
        $this->options['current_background'] = '';

        // Обработка AJAX запроса для получения полей
        if (cmsCore::getInstance()->request->isAjax() && $this->request->has('content_type')) {
            return $this->getContentTypeFields($this->request->get('content_type'));
        }

        // Получаем предсказания в зависимости от источника
        if ($this->options['source'] == 'manual') {
            // Ручной ввод
            if(!empty($this->options['predictions'])){
                $this->options['predictions_array'] = explode("\n", $this->options['predictions']);
                $this->options['predictions_array'] = array_map('trim', $this->options['predictions_array']);
            }
        } else {
            // Из типа контента
            $this->options['predictions_array'] = $this->getPredictionsFromContent();
        }

        // Получаем текущее предсказание из куки
        $prediction_cookie_name = 'random_' . $this->id . '_prediction';
        if (isset($_COOKIE[$prediction_cookie_name])) {
            $this->options['current_prediction'] = $_COOKIE[$prediction_cookie_name];
        }

        // Получаем текущий фон из куки
        $background_cookie_name = 'random_' . $this->id . '_background';
        if (isset($_COOKIE[$background_cookie_name])) {
            $this->options['current_background'] = $_COOKIE[$background_cookie_name];
        }

        // Проверяем ограничения
        if (!empty($this->options['limit_mode']) && $this->options['limit_mode'] != 'none') {
            $this->checkLimits();
        }

        $this->setWrapper('wrapper_plain');

        return $this->options;
    }

    private function getContentTypeFields($content_type) {
        
        $model = cmsCore::getModel('content');
        $fields = $model->getContentFields($content_type);
        
        $prediction_fields = array();
        $background_fields = array();
        
        if ($fields) {
            foreach ($fields as $field) {
                // Поля для предсказаний (текстовые)
                if (in_array($field['type'], array('string', 'html', 'text'))) {
                    $prediction_fields[$field['name']] = $field['title'];
                }
                // Поля для фона (изображения)
                if ($field['type'] == 'image') {
                    $background_fields[$field['name']] = $field['title'];
                }
            }
        }
        
        return array(
            'prediction_fields' => $prediction_fields,
            'background_fields' => $background_fields
        );
    }

    private function getPredictionsFromContent() {
        if (empty($this->options['content_type']) || empty($this->options['prediction_field'])) {
            return array();
        }

        $model = cmsCore::getModel('content');
        
        // Прямой SQL запрос для избежания проблем с фильтрами
        $table_name = '{#}con_' . $this->options['content_type'];
        
        $sql = "SELECT i.* 
                FROM {$table_name} i 
                WHERE 1=1";
        
        // Добавляем фильтр если указан
        if (!empty($this->options['filter'])) {
            $sql .= " AND (" . $this->options['filter'] . ")";
        } else {
            // Стандартные фильтры
            $sql .= " AND i.is_pub = 1";
        }
        
        $sql .= " ORDER BY i.date_pub DESC LIMIT 1000";
        
        $items = $model->db->query($sql);
        
        if (!$items) {
            return array();
        }
        
        $predictions = array();
        foreach ($items as $item) {
            if (!empty($item[$this->options['prediction_field']])) {
                $background = '';
                
                // Получаем фоновое изображение если указано поле
                if (!empty($this->options['background_field']) && !empty($item[$this->options['background_field']])) {
                    $background = $this->parseImageFromContentField($item[$this->options['background_field']]);
                }
                
                $predictions[] = array(
                    'text' => strip_tags($item[$this->options['prediction_field']]),
                    'background' => $background
                );
            }
        }

        return $predictions;
    }

    private function parseImageFromContentField($image_data) {
        if (empty($image_data)) {
            return '';
        }
        
        // Если передан сериализованный формат (строка)
        if (is_string($image_data)) {
            if (strpos($image_data, '{') === 0) {
                $decoded = json_decode($image_data, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $image_data = $decoded;
                }
            } else {
                $parsed = cmsModel::yamlToArray($image_data);
                if (!empty($parsed) && is_array($parsed)) {
                    $image_data = $parsed;
                }
            }
        }
        
        // Если это массив пресетов/размеров
        if (is_array($image_data)) {
            $sizes_priority = array('original', 'big', 'normal', 'small', 'image', 'url');
            foreach ($sizes_priority as $size) {
                if (!empty($image_data[$size])) {
                    return $image_data[$size];
                }
            }
            foreach ($image_data as $val) {
                if (is_string($val) && !empty($val)) {
                    return $val;
                }
            }
            return '';
        }
        
        // Если обычная строка пути
        if (is_string($image_data)) {
            return trim($image_data);
        }
        
        return '';
    }

    private function checkLimits() {
        $interval_minutes = !empty($this->options['limit_interval']) ? (int)$this->options['limit_interval'] : 5;
        $interval_seconds = $interval_minutes * 60;
        $now = time();

        switch ($this->options['limit_mode']) {
            case 'cookie':
                $cookie_name = 'random_' . $this->id . '_time';
                $last_time = isset($_COOKIE[$cookie_name]) ? (int)$_COOKIE[$cookie_name] : 0;
                break;
            default:
                $last_time = 0;
                break;
        }
        
        if ($last_time && ($now - $last_time) < $interval_seconds) {
            $this->options['is_button_enabled'] = false;
            $this->options['next_available_time'] = $last_time + $interval_seconds;
            $this->options['time_left_seconds'] = $this->options['next_available_time'] - $now;
        }
    }
}