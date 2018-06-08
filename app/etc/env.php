<?php
return [
  'backend' => [
    'frontName' => 'admin_190vnp'
  ],
  'crypt' => [
    'key' => '718666ceca4644b934eced10f9e320ce'
  ],
  'db' => [
    'table_prefix' => '',
    'connection' => [
      'default' => [
        'host' => '172.21.238.152',
        'dbname' => 'mekhai_mkp',
        'username' => 'root',
        'password' => 'Me_master_passw0rd',
        'active' => '1'
      ]
    ]
  ],
  'resource' => [
    'default_setup' => [
      'connection' => 'default'
    ]
  ],
  'x-frame-options' => 'SAMEORIGIN',
  'MAGE_MODE' => 'default',
  'session' => [
    'save' => 'db'
  ],
  'cache_types' => [
    'config' => 1,
    'layout' => 1,
    'block_html' => 1,
    'collections' => 1,
    'reflection' => 1,
    'db_ddl' => 1,
    'eav' => 1,
    'customer_notification' => 1,
    'config_integration' => 1,
    'config_integration_api' => 1,
    'full_page' => 1,
    'translate' => 1,
    'config_webservice' => 1,
    'compiled_config' => 1
  ],
  'install' => [
    'date' => 'Wed, 06 Jun 2018 05:25:09 +0000'
  ]
];
