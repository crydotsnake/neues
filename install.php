<?php

namespace FriendsOfRedaxo\neues;

use rex_addon;
use rex_config;
use rex_file;
use rex_media;
use rex_media_service;
use rex_path;
use rex_sql;
use rex_yform_manager_table_api;

/* Tablesets aktualisieren */
if (rex_addon::get('yform') && rex_addon::get('yform')->isAvailable()) {
    rex_yform_manager_table_api::importTablesets(rex_file::get(rex_path::addon(rex_addon::get('neues')->getName(), 'install/tableset.json')));
}

if (!rex_media::get('neues_entry_fallback_image.png')) {

    rex_file::copy(rex_path::addon('neues', '/install/neues_entry_fallback_image.png'), rex_path::media('neues_entry_fallback_image.png'));
    $data = [];
    $data['title'] = 'Aktuelles - Fallback-Image';
    $data['category_id'] = 0;
    $data['file'] = [
        'name' => 'neues_entry_fallback_image.png',
        'path' => rex_path::media('neues_entry_fallback_image.png'),
    ];

    rex_media_service::addMedia($data, false);
}

/* Cronjob installieren */
if (rex_addon::get('cronjob') && rex_addon::get('cronjob')->isAvailable()) {
    $cronjob = array_filter(rex_sql::factory()->getArray("SELECT * FROM rex_cronjob WHERE `type` = 'rex_cronjob_neues_publish'"));
    if (!$cronjob) {
        $query = rex_file::get(rex_path::addon('neues', 'install/rex_cronjob_neues_publish.sql'));
        rex_sql::factory()->setQuery($query);
    }
}

/* URL-Profile installieren */
if (rex_addon::get('url') && rex_addon::get('url')->isAvailable()) {

    if (false === rex_config::get('neues', 'url_profile', false)) {

        $rex_neues_category = array_filter(rex_sql::factory()->getArray("SELECT * FROM rex_url_generator_profile WHERE `table_name` = '1_xxx_rex_neues_category'"));
        if (!$rex_neues_category) {
            $query = rex_file::get(rex_path::addon('neues', 'install/rex_url_profile_neues_category.sql'));
            rex_sql::factory()->setQuery($query);
        }
        $rex_neues_entry = array_filter(rex_sql::factory()->getArray("SELECT * FROM rex_url_generator_profile WHERE `table_name` = '1_xxx_rex_neues_entry'"));
        if (!$rex_neues_entry) {
            $query = rex_file::get(rex_path::addon('neues', 'install/rex_url_profile_neues_entry.sql'));
            rex_sql::factory()->setQuery($query);
        }
        /* URL-Profile wurden bereits einmal installiert, daher nicht nochmals installieren und Entwickler-Einstellungen respektieren */
        rex_config::set('neues', 'url_profile', true);
    }
}
