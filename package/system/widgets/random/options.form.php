<?php

class formWidgetRandomOptions extends cmsForm {

    public function init() {

        return array(

            array(
                'type'   => 'fieldset',
                'title'  => 'Настройки контента',
                'childs' => array(

                    new fieldList('ctype_name', array(
                        'title' => 'Тип контента',
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
                        }
                    )),

                    new fieldList('text_field', array(
                        'title' => 'Поле для текста',
                        'generator' => function($item) {
                            $items = array('' => '');
                            $ctype_name = !empty($item['options']['ctype_name']) ? $item['options']['ctype_name'] : '';
                            if ($ctype_name) {
                                $model = cmsCore::getModel('content');
                                $fields = $model->getContentFields($ctype_name);
                                if ($fields) {
                                    foreach ($fields as $field) {
                                        if (in_array($field['type'], array('string', 'html', 'text'))) {
                                            $items[$field['name']] = $field['title'];
                                        }
                                    }
                                }
                            }
                            return $items;
                        }
                    )),

                    new fieldList('image_field', array(
                        'title' => 'Поле для изображения',
                        'generator' => function($item) {
                            $items = array('' => '');
                            $ctype_name = !empty($item['options']['ctype_name']) ? $item['options']['ctype_name'] : '';
                            if ($ctype_name) {
                                $model = cmsCore::getModel('content');
                                $fields = $model->getContentFields($ctype_name);
                                if ($fields) {
                                    foreach ($fields as $field) {
                                        if ($field['type'] == 'image') {
                                            $items[$field['name']] = $field['title'];
                                        }
                                    }
                                }
                            }
                            return $items;
                        }
                    )),

                    new fieldList('title_field', array(
                        'title' => 'Поле для заголовка (если нет текстового поля)',
                        'generator' => function($item) {
                            $items = array('' => '');
                            $ctype_name = !empty($item['options']['ctype_name']) ? $item['options']['ctype_name'] : '';
                            if ($ctype_name) {
                                $model = cmsCore::getModel('content');
                                $fields = $model->getContentFields($ctype_name);
                                if ($fields) {
                                    foreach ($fields as $field) {
                                        if ($field['type'] == 'string') {
                                            $items[$field['name']] = $field['title'];
                                        }
                                    }
                                }
                            }
                            return $items;
                        }
                    )),

                    new fieldNumber('limit', array(
                        'title' => 'Количество элементов',
                        'default' => 10
                    )),

                    new fieldCheckbox('is_random', array(
                        'title' => 'Случайный порядок',
                        'default' => true
                    ))

                )
            ),

            array(
                'type'   => 'fieldset',
                'title'  => 'Настройки кнопки',
                'childs' => array(

                    new fieldString('button_text', array(
                        'title' => 'Текст кнопки',
                        'default' => 'Получить случайный элемент'
                    )),

                    new fieldColor('button_color', array(
                        'title' => 'Цвет кнопки',
                        'default' => '#3498db'
                    )),

                    new fieldColor('button_text_color', array(
                        'title' => 'Цвет текста кнопки',
                        'default' => '#ffffff'
                    ))

                )
            ),

            array(
                'type'   => 'fieldset',
                'title'  => 'Ограничения',
                'childs' => array(

                    new fieldList('limit_mode', array(
                        'title' => 'Режим ограничения',
                        'items' => array(
                            'none'    => 'Нет ограничений',
                            'cookie'  => 'Использовать Cookie браузера',
                        ),
                        'default' => 'none'
                    )),

                    new fieldNumber('limit_interval', array(
                        'title' => 'Интервал между запросами',
                        'units' => 'минут',
                        'default' => 5,
                        'visible_depend' => array('limit_mode' => array('show' => array('cookie')))
                    ))

                )
            ),

            array(
                'type'   => 'fieldset',
                'title'  => 'Внешний вид',
                'childs' => array(

                    new fieldString('event_name', array(
                        'title' => 'Заголовок виджета',
                        'rules' => array()
                    )),

                    new fieldColor('title_color', array(
                        'title' => 'Цвет заголовка',
                        'default' => '#000000'
                    )),

                    new fieldColor('text_color', array(
                        'title' => 'Цвет текста',
                        'default' => '#333333'
                    )),

                    new fieldCheckbox('enable_background_image', array(
                        'title' => 'Использовать фоновое изображение',
                        'default' => false
                    )),

                    new fieldImage('background_image', array(
                        'title' => 'Фоновое изображение',
                        'options' => [
                            'sizes' => ['original']
                        ],
                        'visible_depend' => array('enable_background_image' => array('show' => array('1')))
                    )),

                    new fieldList('background_size', array(
                        'title' => 'Размер фона',
                        'items' => [
                            'cover'     => 'Cover',
                            'contain'   => 'Contain',
                            'auto'      => 'Auto'
                        ],
                        'default' => 'cover',
                        'visible_depend' => array('enable_background_image' => array('show' => array('1')))
                    )),

                    new fieldNumber('background_opacity', array(
                        'title' => 'Прозрачность фона',
                        'units' => '%',
                        'default' => 50,
                        'visible_depend' => array('enable_background_image' => array('show' => array('1')))
                    )),

                    new fieldColor('background_overlay', array(
                        'title' => 'Цвет затемнения',
                        'default' => '#000000',
                        'visible_depend' => array('enable_background_image' => array('show' => array('1')))
                    ))

                )
            )

        );

    }

}