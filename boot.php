<?php

rex_yform_manager_dataset::setModelClass(
    'rex_neues_entry',
    neues_entry::class
);
rex_yform_manager_dataset::setModelClass(
    'rex_neues_category',
    neues_category::class
);

if (rex::isBackend() && rex_be_controller::getCurrentPage() == "neues/entry" || rex_be_controller::getCurrentPage() == "yform/manager/data_edit") {
    rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) {
        $suchmuster = 'class="###neues-settings-editor###"';
        $ersetzen = rex_config::get("neues", "editor");
        $ep->setSubject(str_replace($suchmuster, $ersetzen, $ep->getSubject()));
    });
}


if (rex_plugin::get('yform', 'rest')->isAvailable() && !rex::isSafeMode()) {
    /* YForm Rest API */
    $rex_neues_entry_route = new \rex_yform_rest_route(
        [
            'path' => '/neues/3/date/',
            'auth' => '\rex_yform_rest_auth_token::checkToken',
            'type' => \neues_entry::class,
            'query' => \neues_entry::query(),
            'get' => [
                'fields' => [
                    'rex_neues_entry' => [
                        'id',
                        'name',
                        'description',
                        'images',
                        'status'
                    ],
                    'rex_neues_category' => [
                        'id',
                        'name'
                    ],
                ],
            ],
            'post' => [
                'fields' => [
                    'rex_neues_entry' => [
                        'name',
                        'description',
                        'images'
                    ],
                ],
            ],
            'delete' => [
                'fields' => [
                    'rex_neues_entry' => [
                        'id',
                    ],
                ],
            ],
        ]
    );

    \rex_yform_rest::addRoute($rex_neues_entry_route);

    /* YForm Rest API */
    $rex_neues_category_route = new \rex_yform_rest_route(
        [
            'path' => '/v0.dev/neues/category/',
            'auth' => '\rex_yform_rest_auth_token::checkToken',
            'type' => \neues_category::class,
            'query' => \neues_category::query(),
            'get' => [
                'fields' => [
                    'rex_neues_category' => [
                        'id',
                        'name'
                    ],
                ],
            ],
            'post' => [
                'fields' => [
                    'rex_neues_category' => [
                        'name'
                    ],
                ],
            ],
            'delete' => [
                'fields' => [
                    'rex_neues_category' => [
                        'id',
                    ],
                ],
            ],
        ]
    );

    \rex_yform_rest::addRoute($rex_neues_category_route);
}



rex_extension::register('YFORM_DATA_LIST', function ($ep) {
    if ($ep->getParam('table')->getTableName()=="rex_neues_entry") {
        $list = $ep->getSubject();

        $list->setColumnFormat(
            'name',
            'custom',
            function ($a) {
                $_csrf_key = rex_yform_manager_table::get('rex_neues_entry')->getCSRFKey();
                $token = rex_csrf_token::factory($_csrf_key)->getUrlParams();

                $params = array();
                $params['table_name'] = 'rex_neues_entry';
                $params['rex_yform_manager_popup'] = '0';
                $params['_csrf_token'] = $token['_csrf_token'];
                $params['data_id'] = $a['list']->getValue('id');
                $params['func'] = 'edit';
    
                return '<a href="'.rex_url::backendPage('neues/entry', $params) .'">'. $a['value'].'</a>';
            }
        );
        $list->setColumnFormat(
            'event_category_id',
            'custom',
            function ($a) {
                $_csrf_key = rex_yform_manager_table::get('rex_neues_category')->getCSRFKey();
                $token = rex_csrf_token::factory($_csrf_key)->getUrlParams();

                $params = array();
                $params['table_name'] = 'rex_neues_category';
                $params['rex_yform_manager_popup'] = '0';
                $params['_csrf_token'] = $token['_csrf_token'];
                $params['data_id'] = $a['list']->getValue('id');
                $params['func'] = 'edit';
    
                $return = [];

                $category_ids = array_filter(explode(",", $a['value']));

                foreach ($category_ids as $category_id) {
                    $event = event_category::get($category_id);
                    if ($event) {
                        $return[] = '<a href="'.rex_url::backendPage('neues/category', $params) .'">'. $event->getName().'</a>';
                    }
                }
                return implode("<br>", $return);
            }
        );
    }
});
