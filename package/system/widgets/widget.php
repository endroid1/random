<?php
class widgetRandom extends cmsWidget {

    public $is_cacheable = false;

    public function run() {

        // Обработка AJAX запроса для получения полей
        if (cmsCore::getInstance()->request->isAjax() && $this->request->has('content_type')) {
            return $this->getContentTypeFields($this->request->get('content_type'));
        }

        $model = cmsCore::getModel('content');

        // Получаем настройки
        $ctype_name = $this->getOption('ctype_name');
        $image_field = $this->getOption('image_field');
        $text_field = $this->getOption('text_field');
        $title_field = $this->getOption('title_field');
        $limit = $this->getOption('limit', 10);
        $is_random = $this->getOption('is_random', true);

        // Инициализируем опции
        $this->options['is_button_enabled'] = true;
        $this->options['next_available_time'] = 0;
        $this->options['time_left_seconds'] = 0;
        $this->options['current_item'] = '';
        $this->options['current_image'] = '';

        // Получаем текущий элемент из куки
        $item_cookie_name = 'random_' . $this->id . '_item';
        $image_cookie_name = 'random_' . $this->id . '_image';
        
        if (isset($_COOKIE[$item_cookie_name])) {
            $this->options['current_item'] = $_COOKIE[$item_cookie_name];
        }
        if (isset($_COOKIE[$image_cookie_name])) {
            $this->options['current_image'] = $_COOKIE[$image_cookie_name];
        }

        // Получаем элементы из типа контента
        $items = $this->getContentItems($model, $ctype_name, $image_field, $text_field, $title_field, $limit, $is_random);

        $this->options['items'] = $items;
        $this->options['button_text'] = $this->getOption('button_text', 'Получить случайный элемент');

        // Проверяем ограничения
        if (!empty($this->options['limit_mode']) && $this->options['limit_mode'] != 'none') {
            $this->checkLimits();
        }

        $this->setWrapper('wrapper_plain');

        return $this->options;
    }

    private function getContentItems($model, $ctype_name, $image_field, $text_field, $title_field, $limit, $is_random) {

        if (!$ctype_name) {
            return array();
        }

        $model->filterEqual('type', $ctype_name);
        $model->filterIsNull('is_deleted');

        // Сортировка
        if ($is_random) {
            $model->orderBy('RAND()');
        } else {
            $model->orderBy('date_pub', 'desc');
        }

        // Лимит
        $model->limit($limit);

        $items = $model->getContentItems($ctype_name);

        if (!$items) {
            return array();
        }

        $result = array();
        foreach ($items as $item) {

            $text = '';
            if ($text_field && !empty($item[$text_field])) {
                $text = strip_tags($item[$text_field]);
            } elseif ($title_field && !empty($item[$title_field])) {
                $text = $item[$title_field];
            }

            $image = '';
            if ($image_field && !empty($item[$image_field])) {
                $image = $this->parseImage($item[$image_field]);
            }

            if ($text) {
                $result[] = array(
                    'text' => $text,
                    'image' => $image
                );
            }
        }

        return $result;
    }

    private function parseImage($image) {

        if (is_array($image)) {
            return !empty($image['url']) ? $image['url'] : '';
        }

        if (is_string($image)) {
            // JSON
            if (strpos($image, '{') === 0) {
                $data = json_decode($image, true);
                if (!empty($data['url'])) {
                    return $data['url'];
                }
            }
            // YAML
            elseif (strpos($image, '---') === 0) {
                $lines = explode("\n", $image);
                foreach ($lines as $line) {
                    if (preg_match('/^(big|normal|small|original):\s*(.+)$/', trim($line), $matches)) {
                        return trim($matches[2]);
                    }
                }
            }
            // Просто путь
            else {
                return $image;
            }
        }

        return '';
    }

    private function getContentTypeFields($content_type) {

        $model = cmsCore::getModel('content');
        $fields = $model->getContentFields($content_type);

        $text_fields = array('' => '');
        $image_fields = array('' => '');

        if ($fields) {
            foreach ($fields as $field) {
                if (in_array($field['type'], array('string', 'html', 'text'))) {
                    $text_fields[$field['name']] = $field['title'];
                }
                if ($field['type'] == 'image') {
                    $image_fields[$field['name']] = $field['title'];
                }
            }
        }

        return array(
            'text_fields' => $text_fields,
            'image_fields' => $image_fields
        );
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