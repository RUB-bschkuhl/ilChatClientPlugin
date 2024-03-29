<?php

declare(strict_types=1);

/**
 * Class ilChatClientConfigGUI
 * @ilCtrl_IsCalledBy ilChatClientConfigGUI: ilObjComponentSettingsGUI
 *
 * @author Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
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
        $input = new ilUriInputGUI($this->pl->txt('config_prompt_url'), "prompt_url");
        $input->setInfo($this->pl->txt('config_prompt_url_info'));
        $confForm->addItem($input);

        $input = new ilUriInputGUI($this->pl->txt('config_upload_url'), "upload_url");
        $input->setInfo($this->pl->txt('config_upload_url_info'));
        $confForm->addItem($input);

        $input = new ilTextInputGUI($this->pl->txt('config_authkey'), "authkey");
        $input->setInfo($this->pl->txt('config_authkey_info'));
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
        $values["prompt_url"] = $this->pl::getValue("prompt_url");
        $values["upload_url"] = $this->pl::getValue("upload_url");
        $values["authkey"] = $this->pl::getValue("authkey");
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
            $this->pl::setValue("prompt_url", $form->getInput("prompt_url"), "text");
            $this->pl::setValue("upload_url", $form->getInput("upload_url"), "text");
            $this->pl::setValue("authkey", $form->getInput("authkey"), "text");
            $this->dic->ui()->mainTemplate()->setOnScreenMessage('success', $this->pl->txt('config_configuration_saved'), true);
        }

        $this->dic->ctrl()->redirect($this,self::CMD_CONFIGURE);

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());

    }
}