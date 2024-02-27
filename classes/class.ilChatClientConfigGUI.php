<?php

declare(strict_types=1);

/**
 * Class ilChatClientConfigGUI
 * @ilCtrl_IsCalledBy ilChatClientConfigGUI: ilObjComponentSettingsGUI
 *
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ilChatClientConfigGUI extends ilPluginConfigGUI
{
    const PLUGIN_CLASS_NAME = ilChatClientPlugin::class;

    const CMD_CONFIGURE = "configure";
    const CMD_UPDATE_CONFIGURE = "updateConfigure";
    const LANG_MODULE = "config";
    const TAB_CONFIGURATION = "configuration";
    private ilChatClientPlugin $pl;
    /**
     * @var \ILIAS\DI\Container|mixed
     */
    private $dic;
    /**
     * @var mixed
     */
    private $tpl;


    /**
     * ilChatClientConfigGUI constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->pl = ilChatClientPlugin::getInstance();
        $this->tpl = $DIC['tpl'];
    }


    /**
     * @inheritDoc
     * @throws ilCtrlException
     */
    public function performCommand(string $cmd): void
    {
        $this->setTabs();

        $next_class = $this->dic->ctrl()->getNextClass($this);

        switch (strtolower($next_class)) {
            default:
                $cmd = $this->dic->ctrl()->getCmd();

                switch ($cmd) {
                    case self::CMD_CONFIGURE:
                    case self::CMD_UPDATE_CONFIGURE:
                        $this->{$cmd}();
                        break;

                    default:
                        break;
                }
                break;
        }
    }


    /**
     *
     * @throws ilCtrlException
     */
    protected function setTabs(): void
    {
        $this->dic->tabs()->addTab(self::TAB_CONFIGURATION, $this->pl->txt("config_configuration"), $this->dic->ctrl()
            ->getLinkTargetByClass(self::class, self::CMD_CONFIGURE));
    }


    /**
     * @return ilPropertyFormGUI
     */
    protected function getConfigForm() : ilPropertyFormGUI
    {
        $this->dic->tabs()->activateTab(self::TAB_CONFIGURATION);
        $confForm = new ilPropertyFormGUI();
        $confForm->setFormAction($this->dic->ctrl()->getFormAction($this));
        $input = new ilNumberInputGUI($this->pl->txt('config_interact_url'), "interact_url");
        $input->setInfo($this->pl->txt('config_interact_url_info'));
        $confForm->addItem($input);

        $input = new ilNumberInputGUI($this->pl->txt('config_upload_url'), "upload_url");
        $input->setInfo($this->pl->txt('config_upload_url_info'));
        $confForm->addItem($input);
        $confForm->addCommandButton(self::CMD_UPDATE_CONFIGURE, $this->pl->txt('config_save'));
        $this->tpl->setContent($confForm->getHTML());

        return $confForm;
    }


    /**
     *
     */
    protected function configure(): void
    {
        $form = $this->getConfigForm();
        $values = array();
        $values["interact_url"] = $this->pl::getValue("interact_url");
        $values["upload_url"] = $this->pl::getValue("upload_url");
        $form->setValuesByArray($values);
        $this->tpl->setContent($form->getHTML());
    }


    /**
     *
     * @throws ilCtrlException
     */
    protected function updateConfigure(): void
    {
        $this->dic->tabs()->activateTab(self::TAB_CONFIGURATION);

        $form = $this->getConfigForm();
        if ($form->checkInput()) {
            $this->pl::setValue("interact_url", $form->getInput("interact_url"), "integer");
            $this->pl::setValue("upload_url", $form->getInput("upload_url"), "integer");
            $this->dic->ui()->mainTemplate()->setOnScreenMessage('success', $this->pl->txt('config_configuration_saved'), true);
        }

        $this->dic->ctrl()->redirect($this,self::CMD_CONFIGURE);

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());

    }
}