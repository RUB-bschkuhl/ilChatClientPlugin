<#1>
<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE  */

/**
 * ChatClient plugin: database update script 
 */ 

$fields = [
    'name' => [
        'type' => 'text',
        'length' => 100,
        'notnull' => true,
    ],
    'value' => [
        'type' => 'text',
        'notnull' => false,
        'default' => null
    ]
 ];

if (!$ilDB->tableExists('excpc_data')) {
    $ilDB->createTable('excpc_data', $fields);
    $ilDB->addPrimaryKey('excpc_data', ['name']);
}

$ilDB->insert('excpc_data', [
    'setting' => ['text', 'interact_url'],
    'value' => null
]);

$ilDB->insert('copg_pgcp_vpco_config', [
    'setting' => ['text', 'upload_url'],
    'value' => null
]);
?>