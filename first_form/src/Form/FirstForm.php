<?php

namespace Drupal\first_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Class FirstForm.
 */
class FirstForm extends FormBase
{
    /**
     * Drupal\Core\Messenger\MessengerInterface
     *
     * @var \Drupal\Core\Messenger\MessengerInterface
     */
    protected $messenger;
    /**
     * Constructs a new FirstForm object.
     */
    public function __construct()
    {
        MessengerInterface $messenger
        $this->messenger = $messenger;
    }
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('messenger')
        );
    }
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'first_form';
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['description'] = [
        '#type' => 'item',
        '#title' => $this->t('Description'),
        '#description' => $this->t('Fill this form to check for eligibility criteria to participate in GCI'),
        ];
        //Takes users name
        $form['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Please enter your full name:'),
        '#required' => true,
        ];
        //Takes users age
        $form['age'] = [
        '#type' => 'number',
        '#title' => $this->t('Please enter your real age:'),
        '#required' => true,
        ];
        //Takes users birthdate
        $form['dob'] = [
        '#type' => 'date',
        '#title' => $this->t('Please enter your Birth date:'),
        '#required' => true,
        ];
        // Takes users gender ' User has to select from a set of options
        $form['gender'] = [
        '#type' => 'select',
        '#title' => $this->t('Kindly inform us of your gender:'),
        '#options' => [
        'male' => $this->t('Male'),
        'female' => $this->t('Female'),
        'other' => $this->t('Other'),
        'prefer not to say' => $this->t('Prefer not to say'),
        ],
        '#required' => true,
        ];
        //Takes users email
        $form['email'] = [
        '#type' => 'email',
        '#title' =>$this->t('Please enter a valid email address'),
        '#required' => true,
        ];
        // Approval checkbox
        $form['approval'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('I agree to the privacy policy of this site'),
        '#description' => $this->t('Please read the privacy policy of this site'),
        ];
        $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        ];
        return $form;
    }
    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $approval = $form_state->getValue('approval');
        if (empty($approval)) {
            // check if privacy policy has been agreed to
            $form_state->setErrorByName('approval', $this->t('Please agree to our privacy policy to continue'));
        }
        // check if user meet age requirements
        $age = $form_state->getValue('age');
        if ($age < 13) {
            // check if user meet age requirements
            $form_state->setErrorByName('age', $this->t('Sorry ,You should be aged 13 and above to participate'));
        }
         // check if user meet age requirements
        if ($age > 17) {
            $form_state->setErrorByName('age', $this->t('Sorry , You should be 17 or less to participate '));
        }
    }
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Output information input by user
        $this->messenger->addMessage('Name: '.$form_state->getValue('name'));
        $this->messenger->addMessage('Age: '.$form_state->getValue('age'));
        $this->messenger->addMessage('Birthday: '.$form_state->getValue('dob'));
        $this->messenger->addMessage('Gender: '.$form_state->getValue('gender'));
        $this->messenger->addMessage('Your email is: '.$form_state->getValue('email'));
        $this->messenger->addMessage('Check your mailbox for confirmation email (spam folder)');
        // Sends confirmation mail to user
        $mailManager = \Drupal::service('plugin.manager.mail');
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $params['context']['subject'] = "Confirmation email";
        $params['context']['message'] = 'Dear user , your form has been submitted';
        $to = $form_state->getValue('email');
        $mailManager->mail('system', 'mail', $to, $langcode, $params);
        // Redirect to home page
        $form_state->setRedirect('<front>');
        return;
    }
}

