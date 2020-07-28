<?php

namespace App\Form;

use App\Entity\Product;
use App\Form\Type\DateTimePickerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Defines the form used to create and manipulate products.
 *
 */
class ProductType extends AbstractType
{
    private $slugger;

    // Form types are services, so you can inject other services in them if needed
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // By default, form fields include the 'required' attribute, which enables
        // the client-side form validation. This means that you can't test the
        // server-side validation errors from the browser. To temporarily disable
        // this validation, set the 'required' attribute to 'false':
        // $builder->add('title', null, ['required' => false, ...]);

        $builder
            ->add('name', null, [
                'attr' => ['autofocus' => true],
                'label' => 'label.title',
            ])
            ->add('summary', TextareaType::class, [
                'help' => 'help.post_summary',
                'label' => 'label.summary',
            ])
            ->add('content', null, [
                'attr' => ['rows' => 20],
                'help' => 'help.post_content',
                'label' => 'label.content',
            ])->add('price', null, [
                'help' => 'help.product_price',
                'label' => 'label.price',
            ])->add('brochure', FileType::class, [
                'label' => array('style' => 'width:300px;'),
                'label' => 'label.product_image_file',
                'attr' => array(
                    'style' => 'width:50%',
                    'placeholder' => 'Product Image'
                ),
                'help' => 'label.file_accepted',
                'mapped' => false, 'required' => false,
                'constraints' => [new File([
                    'maxSize' => '2000k',
                    'mimeTypes' => ['image/png', 'image/jpeg', 'image/jpg'],
                    'mimeTypesMessage' => 'Please upload only images of type [.png,.jpeg,jpg]',
                ])],
            ])->add('publishedAt', DateTimePickerType::class, [
                'label' => 'label.published_at',
                'help' => 'help.post_publication',
            ])
            // form events let you modify information or fields at different steps
            // of the form handling process
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Product */
                $post = $event->getData();
                if (null !== $postName = $post->getName()) {
                    $post->setSlug($this->slugger->slug($postName)->lower());
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
