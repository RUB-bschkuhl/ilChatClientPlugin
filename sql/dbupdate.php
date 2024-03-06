<#1>
    <?php
    /* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE  */

    /**
     * ChatClient plugin: database update script 
     * @var $ilDB ilDB
     */

    $fields = [
        'name' => [
            'type' => 'text',
            'length' => 500,
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
        
        $ilDB->insert('excpc_data', [
            'name' => ['text', 'interact_url'],
            'value' => ['text', null]
        ]);

        $ilDB->insert('excpc_data', [
            'name' => ['text', 'upload_url'],
            'value' => ['text', null]
        ]);
    }


    ?>