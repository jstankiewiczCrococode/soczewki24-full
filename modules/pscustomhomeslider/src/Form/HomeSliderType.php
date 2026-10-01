<?php

namespace PrestaShop\Module\PsCustomHomeSlider\Form;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeSliderType extends TranslatorAwareType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('image', FileType::class, [
                'label' => $this->trans(
                    'Zdjęcie',
                    [],
                    'Modules.Pscustomhomeslider.Admin'
                ),
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/*',
                ],
                'help' => $this->trans(
                    'Zalecany format: JPG, PNG lub WEBP.',
                    [],
                    'Modules.Pscustomhomeslider.Admin'
                ),
            ])

            ->add('url', TextType::class, [
                'label' => $this->trans(
                    'Link',
                    [],
                    'Modules.Pscustomhomeslider.Admin'
                ),
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://...',
                ],
            ])

            ->add('position', IntegerType::class, [
                'label' => $this->trans(
                    'Pozycja',
                    [],
                    'Modules.Pscustomhomeslider.Admin'
                ),
                'required' => true,
                'data' => 0,
            ])

            ->add('active', CheckboxType::class, [
                'label' => $this->trans(
                    'Aktywny',
                    [],
                    'Modules.Pscustomhomeslider.Admin'
                ),
                'required' => false,
            ]);

        $this->addTranslationFields($builder);
    }

    private function addTranslationFields(
        FormBuilderInterface $builder
    ): void {
        $languages = \Language::getLanguages(true);

        foreach ($languages as $language) {
            $idLang = (int) $language['id_lang'];
            $isoCode = strtoupper($language['iso_code']);

            $builder->add(
                'title_' . $idLang,
                TextType::class,
                [
                    'label' => sprintf(
                        '%s - Tytuł',
                        $isoCode
                    ),
                    'required' => false,
                ]
            );

            $builder->add(
                'description_' . $idLang,
                TextareaType::class,
                [
                    'label' => sprintf(
                        '%s - Opis',
                        $isoCode
                    ),
                    'required' => false,
                    'attr' => [
                        'rows' => 4,
                    ],
                ]
            );

            $builder->add(
                'button_text_' . $idLang,
                TextType::class,
                [
                    'label' => sprintf(
                        '%s - Tekst przycisku',
                        $isoCode
                    ),
                    'required' => false,
                ]
            );
        }
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}