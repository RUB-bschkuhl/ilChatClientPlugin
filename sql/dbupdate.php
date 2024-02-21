<#1>
<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE  */

/**
 * ChatClient  plugin: database update script 
 */ 

/**
 * Additional values
 */
if(!$ilDB->tableExists('excpc_data'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),

        'data' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false,
        ),
    );
    $ilDB->createTable('excpc_data', $fields);
    $ilDB->addPrimaryKey('excpc_data', array('id'));
    $ilDB->createSequence('excpc_data');
}
?>