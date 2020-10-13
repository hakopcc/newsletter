<?php

namespace ArcaSolutions\WebBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\IsTrue;

class SendMailType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', ($options['member'] ? 'hidden' : 'text'), ['data' => (($options['member']) ? $options['member']->getFirstName() : null)])
            ->add('email', ($options['member'] ? 'hidden' : 'email'), ['data' => ($options['member'] ? $options['member']->getUsername() : null)])
            ->add('subject', 'text')
            ->add('text', 'textarea');
        if($options['review']=="on"){
            $builder
                ->add('consent', CheckboxType::class, [
                    'label'    => 'I understand that all information I enter here will be stored on the website, but will not be publicly visible nor searchable, except for by the Administrators of the website. I understand that I may be contacted by the Administrator of the website.',
                    'required' => true,
                    'constraints' => new IsTrue(['message' => 'You must agree to the consent terms.'])
                ]);
        }
        /* ModStores Hooks */
        HookFire("sendmailtype_after_buildform", [
            "builder" => &$builder,
        ]);
    }

    /**
     * Sets validation class
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'ArcaSolutions\WebBundle\Entity\SendMail',
            'intention'  => 'sendMail',
            'member'     => null,
            'review'             => '',
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sendMail';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['member'] = $options['member'];
    }
}
