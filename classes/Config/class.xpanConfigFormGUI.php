<?php

/**
 * Class xpanConfigFormGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xpanConfigFormGUI extends ilPropertyFormGUI {

    /**
     * @var ilPanoptoConfigGUI
     */
    protected $parent_gui;
    /**
     * @var ilPanoptoPlugin
     */
    protected $pl;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * xpanConfigFormGUI constructor.
     * @param $parent_gui
     */
    public function __construct($parent_gui) {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->db = $DIC->database();
        $this->pl = ilPanoptoPlugin::getInstance();
        $this->parent_gui = $parent_gui;
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

        $this->initForm();
    }


    /**
     *
     */
    protected function initForm() {
        // GENERAL
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->parent_gui->txt('header_general'));
        $this->addItem($header);

        // Object Title
        $input = new ilTextInputGUI($this->parent_gui->txt(xpanConfig::F_OBJECT_TITLE), xpanConfig::F_OBJECT_TITLE);
        $input->setRequired(true);
        $input->setInfo($this->parent_gui->txt(xpanConfig::F_OBJECT_TITLE . '_info'));
        $this->addItem($input);

        // SOAP API
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->parent_gui->txt('header_soap'));
        $this->addItem($header);

        // API User
        $input = new ilTextInputGUI($this->parent_gui->txt(xpanConfig::F_API_USER), xpanConfig::F_API_USER);
        $input->setInfo($this->parent_gui->txt(xpanConfig::F_API_USER . '_info'));
        $input->setRequired(true);
        $this->addItem($input);
        // hostname
        $input = new ilTextInputGUI($this->parent_gui->txt(xpanConfig::F_HOSTNAME), xpanConfig::F_HOSTNAME);
        $input->setInfo($this->parent_gui->txt(xpanConfig::F_HOSTNAME . '_info'));
        $input->setRequired(true);
        $this->addItem($input);

        // Consumer Key
        $input = new ilTextInputGUI($this->parent_gui->txt(xpanConfig::F_INSTANCE_NAME), xpanConfig::F_INSTANCE_NAME);
        $input->setInfo($this->parent_gui->txt(xpanConfig::F_INSTANCE_NAME . '_info'));
        $input->setRequired(true);
        $this->addItem($input);

        // Admin Secret
        $input = new ilTextInputGUI($this->parent_gui->txt(xpanConfig::F_APPLICATION_KEY), xpanConfig::F_APPLICATION_KEY);
        $input->setInfo($this->parent_gui->txt(xpanConfig::F_APPLICATION_KEY . '_info'));
        $input->setRequired(true);
        $this->addItem($input);

        // User Id
        $input = new ilSelectInputGUI($this->parent_gui->txt(xpanConfig::F_USER_ID), xpanConfig::F_USER_ID);
        $input->setInfo($this->parent_gui->txt(xpanConfig::F_USER_ID . '_info'));
        $input->setRequired(true);
        $input->setOptions(array(
            xpanConfig::SUB_F_LOGIN => $this->parent_gui->txt(xpanConfig::SUB_F_LOGIN),
            xpanConfig::SUB_F_EXT_ACCOUNT => $this->parent_gui->txt(xpanConfig::SUB_F_EXT_ACCOUNT),
            xpanConfig::SUB_F_EMAIL => $this->parent_gui->txt(xpanConfig::SUB_F_EMAIL)
        ));
        $this->addItem($input);

        // REST CLIENT
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->parent_gui->txt('header_rest'));
        $this->addItem($header);

        // REST API user
        $input = new ilTextInputGUI($this->parent_gui->txt(xpanConfig::F_REST_API_USER), xpanConfig::F_REST_API_USER);
        $input->setInfo($this->parent_gui->txt(xpanConfig::F_REST_API_USER . '_info'));
        $input->setRequired(true);
        $this->addItem($input);

        // REST API Password
        $input = new ilTextInputGUI($this->parent_gui->txt(xpanConfig::F_REST_API_PASSWORD), xpanConfig::F_REST_API_PASSWORD);
        $input->setInfo($this->parent_gui->txt(xpanConfig::F_REST_API_PASSWORD . '_info'));
        $input->setRequired(true);
        $this->addItem($input);

        // client name
        $input = new ilTextInputGUI($this->parent_gui->txt(xpanConfig::F_REST_CLIENT_NAME), xpanConfig::F_REST_CLIENT_NAME);
        $input->setInfo($this->parent_gui->txt(xpanConfig::F_REST_CLIENT_NAME . '_info'));
        $input->setRequired(true);
        $this->addItem($input);

        // client id
        $input = new ilTextInputGUI($this->parent_gui->txt(xpanConfig::F_REST_CLIENT_ID), xpanConfig::F_REST_CLIENT_ID);
        $input->setInfo($this->parent_gui->txt(xpanConfig::F_REST_CLIENT_ID . '_info'));
        $input->setRequired(true);
        $this->addItem($input);

        // client secret
        $input = new ilTextInputGUI($this->parent_gui->txt(xpanConfig::F_REST_CLIENT_SECRET), xpanConfig::F_REST_CLIENT_SECRET);
        $input->setInfo($this->parent_gui->txt(xpanConfig::F_REST_CLIENT_SECRET . '_info'));
        $input->setRequired(true);
        $this->addItem($input);

        // Buttons
        $this->addCommandButton(ilPanoptoConfigGUI::CMD_UPDATE,$this->lng->txt('save'));
    }

    /**
     *
     */
    public function fillForm() {
        $array = array();
        foreach ($this->getItems() as $item) {
            $this->getValuesForItem($item, $array);
        }
        $this->setValuesByArray($array);
    }


    /**
     * @param $item
     * @param $array
     *
     * @internal param $key
     */
    private function getValuesForItem($item, &$array) {
        if (self::checkItem($item)) {
            $key = rtrim($item->getPostVar(), '[]');
            if ($key == xpanConfig::F_OBJECT_TITLE) {
                $sql = $this->db->query('select value from lng_data where module = "rep_robj_xpan" and identifier = "rep_robj_xpan_obj_xpan"');
                $value = $this->db->fetchObject($sql)->value;
            } else {
                $value = xpanConfig::getConfig($key);
            }
            $array[$key] = $value;
            if (self::checkForSubItem($item)) {
                foreach ($item->getSubItems() as $subitem) {
                    $this->getValuesForItem($subitem, $array);
                }
                if ($item instanceof ilRadioGroupInputGUI) {
                    foreach ($item->getOptions() as $option) {
                        foreach ($option->getSubItems() as $subitem) {
                            $this->getValuesForItem($subitem, $array);
                        }
                    }
                }
            }
        }
    }


    /**
     * @param $item
     */
    private function saveValueForItem($item) {
        if (self::checkItem($item)) {
            $key = rtrim($item->getPostVar(), '[]');
            $value = $this->getInput($key);

            // exception: object title is stored in lng_data, not in config table
            if ($key == xpanConfig::F_OBJECT_TITLE) {
	            $sql = $this->db->query('select value from lng_data where module = "rep_robj_xpan" and identifier = "rep_robj_xpan_obj_xpan"');
	            $existing = $this->db->fetchObject($sql);

	            if ($existing) {
		            $this->db->update('lng_data',array(
			            'value' => array('text', $value)
		            ), array(
			            'module' => array('text', 'rep_robj_xpan'),
			            'identifier' => array('text', 'rep_robj_xpan_obj_xpan'),
		            ));
	            } else {
		            $this->db->insert('lng_data',array(
			            'lang_key' => array('text', 'de'),
			            'module' => array('text', 'rep_robj_xpan'),
			            'identifier' => array('text', 'rep_robj_xpan_obj_xpan'),
			            'value' => array('text', $value)
		            ));
		            $this->db->insert('lng_data',array(
			            'lang_key' => array('text', 'en'),
			            'module' => array('text', 'rep_robj_xpan'),
			            'identifier' => array('text', 'rep_robj_xpan_obj_xpan'),
			            'value' => array('text', $value)
		            ));
	            }

	            $sql = $this->db->query('select value from lng_data where module = "rep_robj_xpan" and identifier = "rep_robj_xpan_objs_xpan"');
	            $existing = $this->db->fetchObject($sql);

	            if ($existing) {
		            $this->db->update('lng_data',array(
			            'value' => array('text', $value)
		            ), array(
			            'module' => array('text', 'rep_robj_xpan'),
			            'identifier' => array('text', 'rep_robj_xpan_objs_xpan'),
		            ));
	            } else {
		            $this->db->insert('lng_data',array(
			            'lang_key' => array('text', 'de'),
			            'module' => array('text', 'rep_robj_xpan'),
			            'identifier' => array('text', 'rep_robj_xpan_objs_xpan'),
			            'value' => array('text', $value)
		            ));
		            $this->db->insert('lng_data',array(
			            'lang_key' => array('text', 'en'),
			            'module' => array('text', 'rep_robj_xpan'),
			            'identifier' => array('text', 'rep_robj_xpan_objs_xpan'),
			            'value' => array('text', $value)
		            ));
	            }

                return;
            }

            xpanConfig::set($key, $value);
            if (self::checkForSubItem($item)) {
                foreach ($item->getSubItems() as $subitem) {
                    $this->saveValueForItem($subitem);
                }
                if ($item instanceof ilRadioGroupInputGUI) {
                    foreach ($item->getOptions() as $option) {
                        foreach ($option->getSubItems() as $subitem) {
                            $this->saveValueForItem($subitem);
                        }
                    }
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function saveObject() {
        if (!$this->checkInput()) {
            return false;
        }
        foreach ($this->getItems() as $item) {
            $this->saveValueForItem($item);
        }

        return true;
    }


    /**
     * @param $item
     *
     * @return bool
     */
    public static function checkForSubItem($item) {
        return !$item instanceof ilFormSectionHeaderGUI AND !$item instanceof ilMultiSelectInputGUI;
    }


    /**
     * @param $item
     *
     * @return bool
     */
    public static function checkItem($item) {
        return !$item instanceof ilFormSectionHeaderGUI;
    }

}
