<?php

class ARTICLES_CLASS_EditForm extends Form
{
    private $service;
    private $article;

    const FORM_NAME = 'edit-form';

    public function __construct(ARTICLES_BOL_Article $article)
    {
        parent::__construct(self::FORM_NAME);

        $this->service = ARTICLES_BOL_Service::getInstance();
        $this->article = $article;
        $this->setEnctype(FORM::ENCTYPE_MULTYPART_FORMDATA);

        $language = Ow::getLanguage();

        $title = new TextField('title');
        $title->setLabel($language->text('articles', 'title_label'));
        $title->setValue($this->article->title);
        $title->setRequired();
        $this->addElement($title);

        $subtitle = new TextField('subtitle');
        $subtitle->setLabel($language->text('articles', 'subtitle_label'));
        $subtitle->setValue($this->article->subtitle);
        $subtitle->setRequired();
        $this->addElement($subtitle);

        $description = new Textarea('description');
        $description->setLabel($language->text('articles', 'description_label'));
        $description->setValue($this->article->description);
        $description->setRequired();
        $this->addElement($description);

        $image = new FileField('image');
        $image->setLabel($language->text('articles', 'image_label'));
        $image->addAttribute('accept', 'image/jpeg,image/png,image/gif');
        $image->addValidator(new EditAvatarValidator());
        $this->addElement($image);

        $submit = new Submit('go');
        $submit->setValue($language->text('articles', 'submit_button'));
        $this->addElement($submit);
    }

    public function editProcess() {
        if ( OW::getRequest()->isPost() && $this->isValid($_POST)) {

            if ($_FILES['image']['name'] !== '') {
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $name = uniqid() . '_' . time() . '.' . $extension;
                $destination = OW::getPluginManager()->getPlugin('articles')->getUserFilesDir() . $name;
                move_uploaded_file($_FILES['image']['tmp_name'], $destination);
                unlink($this->article->getImageNamePath());
            } else {
                $name = $this->article->image;
            }

            $article = ARTICLES_BOL_ArticleDao::getInstance()->findById($this->article->id);
            $article->title = $_POST['title'];
            $article->subtitle = $_POST['subtitle'];
            $article->description = $_POST['description'];
            $article->image = $name;

            $this->service->editArticleById($article);

            return true;
        }

        return false;
    }
}

class EditAvatarValidator extends OW_Validator {
    public function __construct()
    {
        $this->errorMessage = OW::getLanguage()->text('articles', 'new_image_error_msg');
    }

    public function getJsValidator()
    {
        return "{
            validate : function( value ){
            }
        }";
    }

    function isValid($value)
    {
        if ($_FILES['image']['name'] == '') {
            return 'test.jpg';
        }

        return !empty($_FILES['image']) &&
            $_FILES['image']['error'] === UPLOAD_ERR_OK &&
            in_array($_FILES['image']['type'], array('image/jpeg', 'image/png', 'image/gif'), true) &&
            is_uploaded_file($_FILES['image']['tmp_name']);
    }
}
