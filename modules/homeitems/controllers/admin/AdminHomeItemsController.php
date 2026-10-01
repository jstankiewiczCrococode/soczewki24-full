<?php

require_once dirname(__DIR__, 2) . '/classes/HomeItem.php';

class AdminHomeItemsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'homeitems';
        $this->identifier = 'id_homeitem';
        $this->className = 'HomeItem';
        $this->lang = false;
        $this->bootstrap = true;

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->fields_list = [
            'id_homeitem' => [
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'image' => [
                'title' => 'Obraz',
            ],
            'text' => [
                'title' => 'Tekst',
            ],
            'link' => [
                'title' => 'Link',
            ],
            'position' => [
                'title' => 'Pozycja',
                'type' => 'number',
            ],
            'active' => [
                'title' => 'Aktywny',
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'orderby' => false,
            ],
        ];

        parent::__construct();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddhomeitems')) {
            if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
                $file = $_FILES['image'];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $this->errors[] = 'Nie udało się przesłać obrazka.';
                } else {
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    $extension = strtolower(
                        pathinfo($file['name'], PATHINFO_EXTENSION)
                    );

                    if (!in_array($extension, $allowedExtensions, true)) {
                        $this->errors[] = 'Dozwolone formaty: JPG, JPEG, PNG, WEBP, GIF.';
                    } elseif (!getimagesize($file['tmp_name'])) {
                        $this->errors[] = 'Wybrany plik nie jest prawidłowym obrazem.';
                    } else {
                        $uploadDir = _PS_IMG_DIR_ . 'homeitems/';

                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        $filename = md5(uniqid('', true)) . '.' . $extension;

                        if (move_uploaded_file(
                            $file['tmp_name'],
                            $uploadDir . $filename
                        )) {
                            $_POST['image'] = $filename;
                        } else {
                            $this->errors[] = 'Nie udało się zapisać obrazka.';
                        }
                    }
                }
            }
        }

        parent::postProcess();
    }

    public function renderForm()
    {
        $this->fields_form = [
            'enctype' => 'multipart/form-data',
            'legend' => [
                'title' => 'Element strony głównej',
                'icon' => 'icon-home',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => 'Link',
                    'name' => 'link',
                    'required' => true,
                ],
                [
                    'type' => 'file',
                    'label' => 'Obraz',
                    'name' => 'image',
                    'required' => true,
                    'desc' => 'Wybierz obraz z komputera.',
                    'display_image' => true,
                ],
                [
                    'type' => 'text',
                    'label' => 'Tekst',
                    'name' => 'text',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => 'Pozycja',
                    'name' => 'position',
                    'required' => true,
                    'class' => 'fixed-width-sm',
                ],
                [
                    'type' => 'switch',
                    'label' => 'Aktywny',
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => 'Tak',
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => 'Nie',
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => 'Zapisz',
            ],
        ];

        return parent::renderForm();
    }
}