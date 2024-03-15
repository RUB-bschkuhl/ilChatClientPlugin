<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilChatClientTableGUI extends ilTable2GUI
{
    /**
     * @var ilChatClientPlugin
     */
    protected ilChatClientPlugin $pl;

    /**
     * ilChatClientTableGUI constructor.
     * @throws Exception
     */
    function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC; 
		$ilCtrl = $DIC['ilCtrl'];
		$this->ctrl = $ilCtrl;
		$this->pl = ilChatClientPlugin::getInstance();
        $this->db = $DIC->database();
		parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn("filename", "", "100%");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($this));
        $this->setRowTemplate($this->pl->getDirectory() . '/templates/' . 'tpl.file_list_row.html');
        $this->getMyDataFromDb();
        $this->setTitle("course_files");
    }

    /**
     * Get file data
     */
    function getMyDataFromDb()
    {
        //TODO 
        // [...]
        // https://github.com/ILIAS-eLearning/ILIAS/tree/release_8/Services/Database
        $data[] = array("title" => "test_title", "nr" => "test_nr");         
        // [...]
        $this->setDefaultOrderField("nr");
        $this->setDefaultOrderDirection("asc");
        $this->setData($data);
    }

    /**
     * Fill a single data row.
     */
    protected function fillRow($a_set): void
    {
        // global $lng, $ilCtrl;

        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_NR", $a_set["nr"]);
    }
}
