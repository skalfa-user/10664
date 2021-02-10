<?php

class ARTICLES_CLASS_AddForm extends Form
{
    private $service;

    const FORM_NAME = 'add-form';

    public function __construct()
    {
        parent::__construct(self::FORM_NAME);

        $this->service = ARTICLES_BOL_Service::getInstance();
        $this->setEnctype(FORM::ENCTYPE_MULTYPART_FORMDATA);

        $language = Ow::getLanguage();

        $title = new TextField('title');
        $title->setLabel($language->text('articles', 'title_label'));
        $title->setRequired();
        $this->addElement($title);

        $subtitle = new TextField('subtitle');
        $subtitle->setLabel($language->text('articles', 'subtitle_label'));
        $subtitle->setRequired();
        $this->addElement($subtitle);

        $description = new Textarea('description');
        $description->setLabel($language->text('articles', 'description_label'));
        $description->setRequired();
        $this->addElement($description);

        $image = new FileField('image');
        $image->setLabel($language->text('articles', 'image_label'));
        $image->addAttribute('accept', 'image/jpeg,image/png,image/gif');
        $image->addValidator(new AvatarValidator());
        $this->addElement($image);

        $submit = new Submit('go');
        $submit->setValue($language->text('articles', 'submit_button'));
        $this->addElement($submit);
    }

    public function addProcess() {
        if ( OW::getRequest()->isPost() && $this->isValid($_POST)) {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $name = uniqid() . '_' . time() . '.' . $extension;
            $destination = OW::getPluginManager()->getPlugin('articles')->getUserFilesDir() . $name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination))
            {
                $article = new ARTICLES_BOL_Article();
                $article->title = $_POST['title'];
                $article->subtitle = $_POST['subtitle'];
                $article->description = $_POST['description'];
                $article->image = $name;
                $article->timeStamp = time();
                $article->featured = 0;

                $this->service->addArticle($article);
            }

            return true;
        }

        return false;
    }
}

class AvatarValidator extends OW_Validator {
    public function __construct()
    {
        $this->errorMessage = OW::getLanguage()->text('articles', 'image_error_msg');
    }

    public function getJsValidator()
    {
        return "{
            validate : function( value ){
                if (!value) {
                    throw " . json_encode($this->errorMessage) . ";
                }
            }
        }";
    }

    function isValid($value)
    {
        return !empty($_FILES['image']) &&
            $_FILES['image']['error'] === UPLOAD_ERR_OK &&
            in_array($_FILES['image']['type'], array('image/jpeg', 'image/png', 'image/gif'), true) &&
            is_uploaded_file($_FILES['image']['tmp_name']);
    }
}
