<?php

namespace PrestaShop\Module\PsCustomHomeSlider\Controller\Admin;

use PrestaShop\Module\PsCustomHomeSlider\Form\HomeSliderType;
use PrestaShop\Module\PsCustomHomeSlider\Repository\HomeSliderRepository;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeSliderController extends FrameworkBundleAdminController
{
    private HomeSliderRepository $repository;

    public function __construct(
        HomeSliderRepository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * Lista slajdów.
     */
    public function index(): Response
    {
        $slides = $this->repository->findAll();

        return $this->render(
            '@Modules/pscustomhomeslider/views/templates/admin/index.html.twig',
            [
                'slides' => $slides,
                'layoutTitle' => $this->trans(
                    'Slider strony głównej',
                    [],
                    'Modules.Pscustomhomeslider.Admin'
                ),
            ]
        );
    }

    /**
     * Dodawanie slajdu.
     */
    public function create(Request $request): Response
    {
        $form = $this->createForm(HomeSliderType::class, [
            'active' => true,
            'position' => $this->getNextPosition(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $imageName = $this->uploadImage(
                $form->get('image')->getData()
            );

            if ($imageName === false) {
                $this->addFlash(
                    'error',
                    $this->trans(
                        'Nie udało się przesłać zdjęcia.',
                        [],
                        'Modules.Pscustomhomeslider.Admin'
                    )
                );

                return $this->renderHomeSliderForm(
                    $form,
                    'Dodaj slajd'
                );
            }

            $data['image'] = $imageName;

            $translations = $this->extractTranslations($data);

            $idSlide = $this->repository->create(
                $data,
                $translations
            );

            if (!$idSlide) {
                $this->addFlash(
                    'error',
                    $this->trans(
                        'Nie udało się utworzyć slajdu.',
                        [],
                        'Modules.Pscustomhomeslider.Admin'
                    )
                );

                return $this->renderHomeSliderForm(
                    $form,
                    'Dodaj slajd'
                );
            }

            $this->addFlash(
                'success',
                $this->trans(
                    'Slajd został dodany.',
                    [],
                    'Modules.Pscustomhomeslider.Admin'
                )
            );

            return $this->redirectToRoute(
                'admin_pscustomhomeslider_index'
            );
        }

        return $this->renderHomeSliderForm(
            $form,
            'Dodaj slajd'
        );
    }

    /**
     * Edycja slajdu.
     */
    public function edit(
        int $id,
        Request $request
    ): Response {
        $slide = $this->repository->findOne($id);

        if (!$slide) {
            throw $this->createNotFoundException(
                $this->trans(
                    'Slajd nie istnieje.',
                    [],
                    'Modules.Pscustomhomeslider.Admin'
                )
            );
        }

        $formData = [
            'url' => $slide['url'],
            'position' => $slide['position'],
            'active' => (bool) $slide['active'],
        ];

        foreach ($slide['translations'] as $translation) {
            $idLang = (int) $translation['id_lang'];

            $formData['title_' . $idLang] =
                $translation['title'];

            $formData['description_' . $idLang] =
                $translation['description'];

            $formData['button_text_' . $idLang] =
                $translation['button_text'];
        }

        $form = $this->createForm(
            HomeSliderType::class,
            $formData
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $imageName = $slide['image'];

            $uploadedImage = $form
                ->get('image')
                ->getData();

            if ($uploadedImage instanceof UploadedFile) {
                $newImageName = $this->uploadImage(
                    $uploadedImage
                );

                if ($newImageName === false) {
                    $this->addFlash(
                        'error',
                        $this->trans(
                            'Nie udało się przesłać zdjęcia.',
                            [],
                            'Modules.Pscustomhomeslider.Admin'
                        )
                    );

                    return $this->renderHomeSliderForm(
                        $form,
                        'Edytuj slajd',
                        $slide
                    );
                }

                $this->deleteImage($imageName);

                $imageName = $newImageName;
            }

            $data['image'] = $imageName;

            $translations = $this->extractTranslations(
                $data
            );

            if (!$this->repository->update(
                $id,
                $data,
                $translations
            )) {
                $this->addFlash(
                    'error',
                    $this->trans(
                        'Nie udało się zapisać zmian.',
                        [],
                        'Modules.Pscustomhomeslider.Admin'
                    )
                );

                return $this->renderHomeSliderForm(
                    $form,
                    'Edytuj slajd',
                    $slide
                );
            }

            $this->addFlash(
                'success',
                $this->trans(
                    'Slajd został zaktualizowany.',
                    [],
                    'Modules.Pscustomhomeslider.Admin'
                )
            );

            return $this->redirectToRoute(
                'admin_pscustomhomeslider_index'
            );
        }

        return $this->renderHomeSliderForm(
            $form,
            'Edytuj slajd',
            $slide
        );
    }

    /**
     * Usuwanie slajdu.
     */
    public function delete(
        int $id,
        Request $request
    ): RedirectResponse {
        $slide = $this->repository->findOne($id);

        if (!$slide) {
            $this->addFlash(
                'error',
                $this->trans(
                    'Slajd nie istnieje.',
                    [],
                    'Modules.Pscustomhomeslider.Admin'
                )
            );

            return $this->redirectToRoute(
                'admin_pscustomhomeslider_index'
            );
        }

        if (!$this->isCsrfTokenValid(
            'delete_slide_' . $id,
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->repository->delete($id)) {
            $this->addFlash(
                'error',
                $this->trans(
                    'Nie udało się usunąć slajdu.',
                    [],
                    'Modules.Pscustomhomeslider.Admin'
                )
            );

            return $this->redirectToRoute(
                'admin_pscustomhomeslider_index'
            );
        }

        $this->deleteImage($slide['image']);

        $this->addFlash(
            'success',
            $this->trans(
                'Slajd został usunięty.',
                [],
                'Modules.Pscustomhomeslider.Admin'
            )
        );

        return $this->redirectToRoute(
            'admin_pscustomhomeslider_index'
        );
    }

    /**
     * Formularz jako odpowiedź.
     */
    private function renderHomeSliderForm(
        $form,
        string $title,
        ?array $slide = null
    ): Response {
                return $this->render(
                '@Modules/pscustomhomeslider/views/templates/admin/form.html.twig',
                [
                    'form' => $form->createView(),
                    'title' => $this->trans(
                        $title,
                        [],
                        'Modules.Pscustomhomeslider.Admin'
                    ),
                    'slide' => $slide,
                    'languages' => \Language::getLanguages(true),
                ]
            );
    }

    /**
     * Upload zdjęcia.
     */
    private function uploadImage(
        ?UploadedFile $file
    ): string|false|null {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        if (!in_array(
            $file->getMimeType(),
            $allowedMimeTypes,
            true
        )) {
            return false;
        }

        $uploadDirectory = _PS_IMG_DIR_ . 'pscustomhomeslider/';

        if (!is_dir($uploadDirectory)) {
            if (!mkdir(
                $uploadDirectory,
                0755,
                true
            )) {
                return false;
            }
        }

        $extension = strtolower(
            $file->guessExtension() ?: 'jpg'
        );

        $fileName =
            uniqid('slide_', true) .
            '.' .
            $extension;

        try {
            $file->move(
                $uploadDirectory,
                $fileName
            );
        } catch (\Throwable $e) {
            return false;
        }

        return $fileName;
    }

    /**
     * Usuwa zdjęcie.
     */
    private function deleteImage(
        ?string $imageName
    ): void {
        if (!$imageName) {
            return;
        }

        $path =
            _PS_IMG_DIR_ .
            'pscustomhomeslider/' .
            basename($imageName);

        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Następna pozycja.
     */
    private function getNextPosition(): int
    {
        $slides = $this->repository->findAll();

        if (!$slides) {
            return 0;
        }

        $positions = array_column(
            $slides,
            'position'
        );

        return max($positions) + 1;
    }

    /**
     * Wyciąga dane tłumaczeń z formularza.
     */
    private function extractTranslations(
        array $data
    ): array {
        $translations = [];

        $languages = \Language::getLanguages(true);

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];

            $translations[$idLang] = [
                'title' => $data['title_' . $idLang] ?? '',
                'description' =>
                    $data['description_' . $idLang] ?? '',
                'button_text' =>
                    $data['button_text_' . $idLang] ?? '',
            ];
        }

        return $translations;
    }
}