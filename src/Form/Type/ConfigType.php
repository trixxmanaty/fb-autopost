<?php

namespace App\Form\Type;

use App\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('googleSheetsSheetName', TextType::class, [
                'attr' => ['placeholder' => 'Sheet1'],
            ])
            ->add('googleSheetsReference', TextType::class,[
                'attr' => ['placeholder' => 'https://docs.google.com/spreadsheets/d/1Ya8-yNImAQYPt6mRIsZ8BThbuSGU5P7nIbB8UATqyeY/'],
            ])
            ->add('googleSheetsApiKey', TextType::class)
            ->add('facebookAppId', TextType::class)
            ->add('facebookSecret', TextType::class)
            ->add('facebookPageId', TextType::class)
            ->add('facebookPageToken', TextType::class, [
                'attr' => ['readonly' => true]
            ])
            ->add('facebookLongLivedAccessToken', TextType::class)
            ->add('save', SubmitType::class)
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function(FormEvent $event){
                    $data = $event->getData();
                    if(is_null($data->getGoogleSheetsReference())) {
                        $data->setGoogleSheetsReference('https://docs.google.com/spreadsheets/d/1_E8O1HQtY0DL8H7N8m5z5L9hYWNQ6j5nz7rYdljo0eE/');
                    }
                }
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}