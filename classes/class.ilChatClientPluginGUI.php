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
    protected ilTree $tree;

    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC['tpl'];
        $this->pl = ilChatClientPlugin::getInstance();
        $this->tree = $DIC->repositoryTree();
    }

    /**
     * @inheritDoc
     */
    public function getRepositoryObjectChildren(
        int $ref_id,
        array $types,
        int $max_depth = PHP_INT_MAX,
        int $depth = 0
    ): Generator {
        if ($depth === $max_depth) {
            return;
        }

        $container_types = $this->getContainerObjectTypes();
        $combined_types = array_unique(array_merge($container_types, $types));
        $children = $this->tree->getChildsByTypeFilter($ref_id, $combined_types);

        foreach ($children as $container_or_candidate) {
            $object_ref_id = (int) $container_or_candidate['ref_id'];
            // object is file
            if (in_array($container_or_candidate['type'], $types, true)) {
                $fileobject_ref_id = (int) $container_or_candidate['ref_id'];

                $object = ilObjectFactory::getInstanceByRefId($fileobject_ref_id, false);
                if (false !== $object) {
                    yield $object_ref_id => $object;
                }
            }
            // object is a container object at this point.
            yield from $this->getRepositoryObjectChildren($object_ref_id, $types, $max_depth, $depth + 1);
        }
    }

    protected function getContainerObjectTypes(): array
    {
        return ['crs', 'cat', 'grp', 'fold', 'itgr'];
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

        $tpl = $pl->getTemplate("tpl.chat.html");

        $course_id = $this->getParentCourseId();
        $course_file_array = $this->getCourseFiles($course_id);
        $file_refs = $this->getFileRefs($course_file_array);
        $course_file_array_json = json_encode($file_refs);

        $tpl->setVariable('file_content', $course_file_array_json);
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * @param array<ilObjFile> $fileObjs
     */
    function getFileRefs($fileObjs): array
    {
        // generate anchors for files
        $objRefs = [];

        if (!empty($fileObjs)) {
            foreach ($fileObjs as $fileObj) {
                // TODO security
                $this->ctrl->setParameter($this, 'id', $fileObj->getId());
                $url = $this->ctrl->getLinkTargetByClass(
                    array('ilUIPluginRouterGUI', 'ilChatClientPluginGUI'),
                    'downloadFile'
                );

                $content_file = new stdClass;
                $content_file->filename = $fileObj->getPresentationTitle();
                $content_file->mimetype = $fileObj->getFileType();
                $content_file->id = $fileObj->getId();

                array_push($objRefs, $content_file);
            }
        }
        return $objRefs;
    }

    function getParentCourseId(): int
    {
        $node_id = $_GET['ref_id'];
        $crs_ref_id = $this->tree->checkForParentType($node_id, "crs");

        return $crs_ref_id;
    }

    function getCourseFiles($course_id): array
    {
        $files = $this->getRepositoryObjectChildren($course_id, ['file']);
        $stored_files = [];

        foreach ($files as $file) {
            $stored_files[] = $file;
        }
        return $stored_files;
    }

    /**
     * download file of file lists
     */
    function downloadFile(): void
    {
        $file_id = (int) $_GET['id'];
        $fileObj = new ilObjFile($file_id, false);
        $fileObj->sendFile();
    }
}
