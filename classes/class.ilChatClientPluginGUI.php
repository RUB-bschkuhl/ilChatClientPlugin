<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * ChatClient GUI
 * @author Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @ilCtrl_isCalledBy ilChatClientPluginGUI: ilPCPluggedGUI
 * @ilCtrl_isCalledBy ilChatClientPluginGUI: ilUIPluginRouterGUI
 */
class ilChatClientPluginGUI extends ilPageComponentPluginGUI
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilChatClientPlugin $pl;

    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC['tpl'];
        $this->pl = new ilChatClientPlugin($this->db, $DIC["component.repository"], ilChatClientPlugin::PLUGIN_ID);
        // $this->pl = new ilChatClientPlugin();
    }


    /**
     * Show a data table
     */
    // function getDataList(): string
    // {
    //     include_once("./Customizing/global/plugins/Services/COPage/PageComponent/ChatClient/classes/class.ilChatClientTableGUI.php");
    //     $table_gui = new ilChatClientTableGUI($this, "getDataList");
    //     return $table_gui->getHTML();
    // }

    /**
     * Show a data table
     */
    function getDataList()
    {
        global $tpl;
        include_once("./Customizing/global/plugins/Services/COPage/PageComponent/ChatClient/classes/class.ilChatClientTableGUI.php");
        $table_gui = new ilChatClientTableGUI($this, "getDataList");
        $html = $table_gui->getHTML();
        $tpl->setContent($html);
    }

    /**
     * This function executes all commands routed to the class.
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            default:
                // perform valid commands
                $cmd = $this->ctrl->getCmd();
                if (in_array($cmd, array("create", "save", "edit", "update", "cancel", "downloadFile"))) {
                    $this->$cmd();
                }
                break;
        }
    }

    /**
     * This function must generate the creation dialog. 
     */
    public function insert(): void
    {
        $form = $this->initForm(true);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * This function uses the form input to create the element.
     */
    public function create(): void
    {
        $form = $this->initForm(true);
        if ($this->saveForm($form, true)) {;
        } {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
            $this->returnToParent();
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     *  This function must generate the editing dialog.
     */
    public function edit(): void
    {
        $form = $this->initForm();

        $this->tpl->setContent($form->getHTML());
    }

    public function update(): void
    {
        $form = $this->initForm(false);
        if ($this->saveForm($form, false)) {;
        } {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
            $this->returnToParent();
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init editing form
     */
    protected function initForm(bool $a_create = false): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        // page value
        $page_value = new ilTextInputGUI('page_value', 'page_value');
        $page_value->setMaxLength(40);
        $page_value->setSize(40);
        $page_value->setRequired(true);
        $form->addItem($page_value);

        $interacturl = $this->pl::getValue("interact_url");
        $uploadurl = $this->pl::getValue("upload_url");

        $info = new ilNonEditableValueGUI($this->lng->txt("interact_url"));
        $info->setValue($interacturl);
        $form->addItem($info);

        $info = new ilNonEditableValueGUI($this->lng->txt("upload_url"));
        $info->setValue($uploadurl);
        $form->addItem($info);

        // page info values
        foreach ($this->getPageInfo() as $key => $value) {
            $info = new ilNonEditableValueGUI($key);
            $info->setValue($value);
            $form->addItem($info);
        }

        // save and cancel commands
        if ($a_create) {
            $this->addCreationButton($form);
            $form->addCommandButton("cancel", $this->lng->txt("cancel"));
            $form->setTitle($this->plugin->getPluginName());
        } else {
            $prop = $this->getProperties();
            $page_value->setValue($prop['page_value']);

            $form->addCommandButton("update", $this->lng->txt("save"));
            $form->addCommandButton("cancel", $this->lng->txt("cancel"));
            $form->setTitle($this->plugin->getPluginName());
        }

        $form->setFormAction($this->ctrl->getFormAction($this));
        return $form;
    }

    protected function saveForm(ilPropertyFormGUI $form, bool $a_create): bool
    {
        if ($form->checkInput()) {
            $properties = $this->getProperties();

            // value saved in the page
            $properties['page_value'] = $form->getInput('page_value');

            // example save input
            // $id = $properties['upload_url'] ?? null;
            // if (empty($id)) {
            //     $id = $this->plugin->saveData($form->getInput('upload_url'));
            //     $properties['upload_url'] = $id;
            // } else {
            //     $this->plugin->updateData($id, $form->getInput('upload_url'));
            // }

            if ($a_create) {
                return $this->createElement($properties);
            } else {
                return $this->updateElement($properties);
            }
        }

        return false;
    }

    /**
     * Cancel
     */
    public function cancel()
    {
        $this->returnToParent();
    }

    /**
     * Get HTML for element
     * @param string    page mode (edit, presentation, print, preview, offline)
     * @return string   html code
     */
    public function getElementHTML(string $a_mode, array $a_properties, string $a_plugin_version): string
    {
        $pl = $this->getPlugin();

        $display = array_merge($a_properties, $this->getPageInfo());

        $tpl = $pl->getTemplate("tpl.chat.html");

        $tpl->setVariable("CHAT_ID", "1234");
        $this->getDataList();
        $tpl->parseCurrentBlock();
        return $tpl->get();
        // show properties stores in the page
        // $html = '<pre>' . print_r($display, true);

        // // show additional data
        // if (!empty($a_properties['additional_data_id'])) {
        //     $data = $pl->getData($a_properties['additional_data_id']);
        //     $html .= 'Data: ' . $data . "\n";
        // }

        // // show uploaded file
        // if (!empty($a_properties['page_file'])) {
        //     try {
        //         $fileObj = new ilObjFile($a_properties['page_file'], false);

        //         // security
        //         $_SESSION[__CLASS__]['allowedFiles'][$fileObj->getId()] = true;

        //         $this->ctrl->setParameter($this, 'id', $fileObj->getId());
        //         $url = $this->ctrl->getLinkTargetByClass(array('ilUIPluginRouterGUI', 'ilChatClientPluginGUI'),
        //             'downloadFile');
        //         $title = $fileObj->getPresentationTitle();

        //     } catch (Exception $e) {
        //         $url = "";
        //         $title = $e->getMessage();
        //     }

        //     $html .= 'File: <a href="' . $url . '">' . $title . '</a>' . "\n";
        // }

        // // Show listened event
        // if ($event = ($_SESSION['excpc_listened_event'] ?? null)) {
        //     $html .= "\n";
        //     $html .= 'Last Auth Event: ' . ilDatePresentation::formatDate(new ilDateTime($event['time'], IL_CAL_UNIX));
        //     $html .= ' ' . $event['event'];
        // }

        // $html .= '</pre>';
    }

    function getCourseFiles(): void
    {
    }

    /**
     * download file of file lists
     */
    function downloadFile(): void
    {
        $file_id = (int) $_GET['id'];
        if ($_SESSION[__CLASS__]['allowedFiles'][$file_id]) {
            $fileObj = new ilObjFile($file_id, false);
            $fileObj->sendFile();
        } else {
            throw new ilException('not allowed');
        }
    }

    /**
     * Get information about the page that embeds the component
     * @return    array    key => value
     */
    public function getPageInfo(): array
    {
        return array(
            'page_id' => $this->plugin->getPageId(),
            'parent_id' => $this->plugin->getParentId(),
            'parent_type' => $this->plugin->getParentType()
        );
    }
}
