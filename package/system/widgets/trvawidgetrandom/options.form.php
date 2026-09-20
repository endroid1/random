<?php

class formWidgettrvawidgetrandomOptions extends cmsForm {

    public function init($options, $template_name) {

        return array(
            'prophecy' => array(
                'type'   => 'fieldset',
                'title'  => LANG_WD_TRVAWIDGETRANDOM_TITLE,
                'childs' => array(

                    new fieldList('options:source', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_SOURCE,
                        'items' => array(
                            'manual' => LANG_WD_TRVAWIDGETRANDOM_SOURCE_MANUAL,
                            'content' => LANG_WD_TRVAWIDGETRANDOM_SOURCE_CONTENT,
                        ),
                        'default' => 'manual'
                    )),

                    // Ручной ввод предсказаний
                    new fieldText('options:predictions', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_PREDICTIONS,
                        'hint' => LANG_WD_TRVAWIDGETRANDOM_PREDICTIONS_HINT,
                        'rules' => array(),
                        'visible_depend' => array('options:source' => array('show' => array('manual')))
                    )),

                    // Настройки для типа контента
                    new fieldList('options:content_type', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_CONTENT_TYPE,
                        'generator' => function($item) {
                            $model = cmsCore::getModel('content');
                            $types = $model->getContentTypes();
                            $items = array('' => '');
                            if ($types) {
                                foreach ($types as $type) {
                                    $items[$type['name']] = $type['title'];
                                }
                            }
                            return $items;
                        },
                        'visible_depend' => array('options:source' => array('show' => array('content')))
                    )),

                    new fieldList('options:prediction_field', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_PREDICTION_FIELD,
                        'generator' => function($item) {
                            $items = array('' => '');
                            
                            // Получаем тип контента из параметров
                            $content_type = !empty($item['options']['content_type']) ? 
                                          $item['options']['content_type'] : 
                                          (isset($_POST['content_type']) ? $_POST['content_type'] : '');
                            
                            if ($content_type) {
                                $model = cmsCore::getModel('content');
                                $fields = $model->getContentFields($content_type);
                                if ($fields) {
                                    foreach ($fields as $field) {
                                        // Разрешаем текстовые поля и HTML
                                        if (in_array($field['type'], array('string', 'html', 'text'))) {
                                            $items[$field['name']] = $field['title'];
                                        }
                                    }
                                }
                            }
                            return $items;
                        },
                        'visible_depend' => array('options:source' => array('show' => array('content')))
                    )),

                    new fieldList('options:background_field', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_FIELD,
                        'generator' => function($item) {
                            $items = array('' => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_IMAGE);
                            
                            // Получаем тип контента из параметров
                            $content_type = !empty($item['options']['content_type']) ? 
                                          $item['options']['content_type'] : 
                                          (isset($_POST['content_type']) ? $_POST['content_type'] : '');
                            
                            if ($content_type) {
                                $model = cmsCore::getModel('content');
                                $fields = $model->getContentFields($content_type);
                                if ($fields) {
                                    foreach ($fields as $field) {
                                        if ($field['type'] == 'image') {
                                            $items[$field['name']] = $field['title'];
                                        }
                                    }
                                }
                            }
                            return $items;
                        },
                        'visible_depend' => array('options:source' => array('show' => array('content')))
                    )),

                    new fieldString('options:filter', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_FILTER,
                        'hint' => 'Например: i.is_pub=1',
                        'visible_depend' => array('options:source' => array('show' => array('content')))
                    )),

                    new fieldString('options:event_name', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_EVENT_NAME,
                        'rules' => array()
                    )),

                    new fieldString('options:button_text', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_BUTTON_TEXT,
                        'default' => LANG_WD_TRVAWIDGETRANDOM_BUTTON_TEXT_DEFAULT
                    )),

                    new fieldColor('options:title_color', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_TITLE_COLOR,
                        'default' => '#000000'
                    )),

                    new fieldColor('options:text_color', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_TEXT_COLOR,
                        'default' => '#333333'
                    )),

                    new fieldColor('options:button_color', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_BUTTON_COLOR,
                        'default' => '#3498db'
                    )),

                    new fieldColor('options:button_text_color', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_BUTTON_TEXT_COLOR,
                        'default' => '#ffffff'
                    )),

                )
            ),
            'limits' => array(
                'type'   => 'fieldset',
                'title'  => 'Ограничения показа',
                'childs' => array(

                    new fieldList('options:limit_mode', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_LIMIT_MODE,
                        'items' => array(
                            'none'    => LANG_WD_TRVAWIDGETRANDOM_LIMIT_MODE_NONE,
                            'cookie'  => LANG_WD_TRVAWIDGETRANDOM_LIMIT_MODE_COOKIE,
                        ),
                        'default' => 'none'
                    )),

                    new fieldNumber('options:limit_interval', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_LIMIT_INTERVAL,
                        'hint' => LANG_WD_TRVAWIDGETRANDOM_LIMIT_INTERVAL_HINT,
                        'units' => LANG_WD_TRVAWIDGETRANDOM_LIMIT_INTERVAL_UNITS,
                        'default' => 5,
                        'visible_depend' => array('options:limit_mode' => array('show' => array('cookie')))
                    )),

                )
            ),
            'background' => array(
                'type'   => 'fieldset',
                'title'  => 'Настройка фона',
                'childs' => array(

                    new fieldCheckbox('options:enable_background_image', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_ENABLE_BACKGROUND,
                        'default' => false
                    )),
            
                    new fieldImage('options:background_image', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_IMAGE,
                        'options' => [
                            'sizes' => ['original']
                        ],
                        'visible_depend' => [
                            'options:enable_background_image' => ['show' => ['1']],
                            'options:source' => ['show' => ['manual']]
                        ]
                    )),
            
                    new fieldList('options:background_size', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_SIZE,
                        'items' => [
                            'cover'     => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_SIZE_COVER,
                            'contain'   => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_SIZE_CONTAIN,
                            'auto'      => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_SIZE_AUTO,
                            '100% 100%' => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_SIZE_100,
                        ],
                        'default' => 'cover',
                        'visible_depend' => [
                            'options:enable_background_image' => ['show' => ['1']]
                        ]
                    )),

                    new fieldList('options:background_position', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_POSITION,
                        'items' => [
                            'center'        => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_POSITION_CENTER,
                            'top'           => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_POSITION_TOP,
                            'bottom'        => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_POSITION_BOTTOM,
                            'left'          => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_POSITION_LEFT,
                            'right'         => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_POSITION_RIGHT,
                            'top left'      => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_POSITION_TOP_LEFT,
                            'top right'     => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_POSITION_TOP_RIGHT, 
                            'bottom left'   => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_POSITION_BOTTOM_LEFT,
                            'bottom right'  => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_POSITION_BOTTOM_RIGHT
                        ],
                        'default' => 'center',
                        'visible_depend' => [
                            'options:enable_background_image' => ['show' => ['1']]
                        ]
                    )),

                    new fieldList('options:background_repeat', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_REPEAT,
                        'items' => [
                            'no-repeat' => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_REPEAT_NO,
                            'repeat'    => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_REPEAT_YES,
                            'repeat-x'  => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_REPEAT_X,
                            'repeat-y'  => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_REPEAT_Y
                        ],
                        'default' => 'no-repeat',
                        'visible_depend' => [
                            'options:enable_background_image' => ['show' => ['1']]
                        ]
                    )),

                    new fieldNumber('options:background_opacity', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_OPACITY,
                        'units' => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_OPACITY_UNITS,
                        'default' => 50,
                        'visible_depend' => [
                            'options:enable_background_image' => ['show' => ['1']]
                        ]
                    )),

                    new fieldColor('options:background_overlay', array(
                        'title' => LANG_WD_TRVAWIDGETRANDOM_BACKGROUND_OVERLAY,
                        'default' => '#000000',
                        'visible_depend' => [
                            'options:enable_background_image' => ['show' => ['1']]
                        ]
                    )),
                )
            ),
        );
    }
}