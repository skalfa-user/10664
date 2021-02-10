<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
/**
 * Admin advers controller class.
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_plugins.advertisement.controllers
 * @since 1.0
 */
class LPAGE_CTRL_Admin extends ADMIN_CTRL_Abstract {

    const CONTROL_IMAGE_MAX_FILE_SIZE_MB = 2;

    public function index() {

        OW::getNavigation()->activateMenuItem("admin_plugins", "admin", "sidebar_menu_plugins_installed");
        $language = OW::getLanguage();
        $plugin = OW::getPluginManager()->getPlugin("lpage");

        $this->setPageHeading($language->text("lpage", "admin_settings_heading"));

        $form = new Form("settings");
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $this->addForm($form);
        $data = json_decode(OW::getConfig()->getValue("lpage", "settings"), true);

        $label = new TextField("label");
        $label->setLabel($language->text("lpage", "settings_label_label"));
        $label->setDescription($language->text("lpage", "settings_label_desc"));
        $label->setValue($data["label"]);
        $form->addElement($label);

        $userfilesPath = $plugin->getUserFilesDir();
        $userfilesUrl = $plugin->getUserFilesUrl();
        $staticUrl = $plugin->getStaticCssUrl();

        $logo = new LPAGE_ImageField("logoFile");
        $logo->setLabel($language->text("lpage", "settings_logo_code_label"));
        $logo->setDescription($language->text("lpage", "settings_logo_code_desc"));
        $logo->setValue(empty($data["logoFile"]) ? $staticUrl . "images/logo.png" : $data["logoFile"]);
        $form->addElement($logo);

        $bg = new LPAGE_ImageField("bgFile");
        $bg->setLabel($language->text("lpage", "settings_bg_code_label"));
        $bg->setDescription($language->text("lpage", "settings_bg_code_desc"));
        $bg->setValue(empty($data["bgFile"]) ? $staticUrl . "images/bg.jpg" : $data["bgFile"]);
        $form->addElement($bg);


        $submit = new Submit("save");
        $submit->setValue($language->text("admin", "save_btn_label"));
        $form->addElement($submit);

        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                if (!empty($_FILES["logoFile"]["tmp_name"])) {
                    try {
                        $data["logoFile"] = $userfilesUrl . $this->addImage($_FILES["logoFile"], "logo");
                    } catch (LogicException $e) {
                        OW::getFeedback()->error($e->getMessage());
                    }
                }
                if (!empty($_FILES["bgFile"]["tmp_name"])) {
                    try {
                        $data["bgFile"] = $userfilesUrl . $this->addImage($_FILES["bgFile"], "bg");
                    } catch (LogicException $e) {
                        OW::getFeedback()->error($e->getMessage());
                    }
                }

                $data["label"] = trim($_POST["label"]);
            } else {
                OW::getFeedback()->error($language->text("admin", "settings_submit_error_message"));
            }

            OW::getConfig()->saveConfig("lpage", "settings", json_encode($data));
            OW::getFeedback()->info($language->text("admin", "settings_submit_success_message"));
            $this->redirect();
        }
    }

    public function uninstall() {
        $this->setPageHeading(OW::getLanguage()->text("lpage", "admin_uninstall_blocked_heading"));
    }

    public function resetImage() {
        $data = json_decode(OW::getConfig()->getValue("lpage", "settings"), true);
        $name = trim($_GET['name']);

        if (!empty($data[$name])) {
            unset($data[$name]);
        }

        OW::getConfig()->saveConfig("lpage", "settings", json_encode($data));
        OW::getFeedback()->info(OW::getLanguage()->text("admin", "settings_submit_success_message"));
        $this->redirectToAction("index");
    }

    private function addImage($file, $name) {

        $language = OW::getLanguage();

        if ($file['error'] != UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $error = $language->text('base', 'upload_file_max_upload_filesize_error');
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $error = $language->text('base', 'upload_file_file_partially_uploaded_error');
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $error = $language->text('base', 'upload_file_no_file_error');
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $error = $language->text('base', 'upload_file_no_tmp_dir_error');
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    $error = $language->text('base', 'upload_file_cant_write_file_error');
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $error = $language->text('base', 'upload_file_invalid_extention_error');
                    break;

                default:
                    $error = $language->text('base', 'upload_file_fail');
            }

            throw new LogicException($error);
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new LogicException($language->text('base', 'upload_file_fail'));
        }

        if ((int) $file['size'] > self::CONTROL_IMAGE_MAX_FILE_SIZE_MB * 1024 * 1024) {
            throw new LogicException($language->text('base', 'upload_file_max_upload_filesize_error'));
        }

        if (!UTIL_File::validateImage($file['name'])) {
            throw new LogicException($language->text('admin', 'no_photo_uploaded'));
        }

        $ext = UTIL_File::getExtension($file['name']);
        $imageName = $name . "." . $ext;

        //cloudfiles header fix for amazon : need right extension to upload file with right header
        $newTempName = $file['tmp_name'] . '.' . $ext;
        rename($file['tmp_name'], $newTempName);
        OW::getStorage()->copyFile($newTempName, OW::getPluginManager()->getPlugin("lpage")->getUserFilesDir() . $imageName);

        if (file_exists($newTempName)) {
            unlink($newTempName);
        }

        return $imageName;
    }

}

class LPAGE_ImageField extends FormElement {

    private $mobile;

    public function __construct($name) {
        parent::__construct($name);
    }

    function setMobile($mobile) {
        $this->mobile = (bool) $mobile;
    }

    public function getValue() {
        return isset($_FILES[$this->getName()]) ? $_FILES[$this->getName()] : null;
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput($params = null) {
        parent::renderInput($params);

        $output = '';

        if ($this->value !== null && ( trim($this->value) !== 'none' )) {

            $randId = 'if' . rand(10, 10000000);

            $script = "$('#" . $randId . "').click(function(){
                new OW_FloatBox({\$title:'" . OW::getLanguage()->text('admin', 'themes_settings_graphics_preview_cap_label') . "', \$contents:$('#image_view_" . $this->getName() . "'), width:'800px'});
            });";

            OW::getDocument()->addOnloadScript($script);

            $output .= '<div class="clearfix"><a id="' . $randId . '" href="javascript://" class="theme_control theme_control_image" style="background-image:url(' . $this->value . ');"></a>
                <div style="float:left;padding:10px 0 0 10px;"><a href="javascript://" onclick="window.location=\'' . OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('LPAGE_CTRL_Admin', 'resetImage'), array('name' => $this->getName())) . '\'">' . OW::getLanguage()->text('admin', 'themes_settings_reset_label') . '</a></div></div>
                <div style="display:none;"><div class="preview_graphics" id="image_view_' . $this->getName() . '" style="background-image:url(' . $this->value . ')"></div></div>';
        }

        $output .= '<input type="file" accept="image/*" name="' . $this->getName() . '" />';

        return $output;
    }

}
