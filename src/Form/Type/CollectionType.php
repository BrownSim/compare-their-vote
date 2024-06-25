<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType as BaseCollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label_add' => 'form.add',
            'label_remove' => 'form.delete',
        ]);

        $resolver->setNormalizer('entry_options', function (Options $options, $value) {
            return array_merge([
                'block_prefix' => 'base_collection_entry',
            ], $value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['label_add'] = $options['label_add'];
        $view->vars['label_remove'] = $options['label_remove'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return BaseCollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'base_collection';
    }
}
