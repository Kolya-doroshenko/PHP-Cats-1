<?php

namespace Drupal\nekromant512\Form;

use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides an Cat form.
 */
class CatForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cat_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_message"></div>',
    ];
    $form['name'] = [
      '#title' => t("Your cat's name:"),
      '#type' => 'textfield',
      '#size' => 32,
      '#description' => t("Name should be at least 2 characters and less than 32 characters"),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxValidName',
        'event' => 'keyup',
      ],
    ];
    $form['message2'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_email"></div>',
    ];
    $form['email'] = [
      '#title' => t("Email:"),
      '#type' => 'email',
      '#description' => t("example@gmail.com"),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxValidEmail',
        'event' => 'keyup',
      ],
    ];
    $form['message3'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_image"></div>',
    ];
    $form['image'] = [
      '#title' => t("Image:"),
      '#type' => 'managed_file',
      '#upload_location' => 'public://module-images',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
      '#description' => t("insert image below size of 2MB. Supported formats: png jpg jpeg."),
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add cat'),
      '#ajax' => [
        'callback' => '::setMessage',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if(!empty($form_state->getValue('image')[0])) {
      $isImage = TRUE;
    }
    else{
      $isImage = FALSE;
    }
    if($this->validName($form, $form_state) && $this->validEmail($form, $form_state) && $isImage){
    $connection = \Drupal::service('database');
    $file = File::load($form_state->getValue('image')[0]);
    $file->setPermanent();
    $file->save();
    $result = $connection->insert('nekromant512')
      ->fields([
        'name' => $form_state->getValue('name'),
        'mail' => $form_state->getValue('email'),
        'uid' => $this->currentUser()->id(),
        'created' => date('d/m/Y G:i:s', strtotime('+3 hour')),
        'image' => $form_state->getValue('image')[0],
      ])
      ->execute();
    }
  }

  /**
   * Function that validate name.
   */
  public function validName(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    if (strlen($name) < 2 || strlen($name) > 32) {
      return FALSE;
    }
    else{
      return TRUE;
    }
  }

  /**
   * Function that validate name input with ajax.
   */
  public function ajaxValidName(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $name = $form_state->getValue('name');
    if($this->validName($form, $form_state) == FALSE){
      $response->addCommand(
        new HtmlCommand(
          '.result_message',
          '<div class="my_top_message">' . $this->t('Not correct name')
        )
      );
    }
    else {
      $response->addCommand(
        new HtmlCommand(
          '.result_message',
          '<div class="my_top_message">' . $this->t('You cat name: %name.', ['%name' => $name])
        )
      );
    }
    return $response;
  }

  /**
   * Function that validate email.
   */
  public function validEmail(array &$form, FormStateInterface $form_state) {
    if(filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      return TRUE;
    }
    else{
      return FALSE;
    }
  }

  /**
   * Function that validate email input with ajax.
   */
  public function ajaxValidEmail(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $email = $form_state->getValue('email');
    if ($this->validEmail($form, $form_state)) {
      $response->addCommand(
        new HtmlCommand(
          '.result_email',
          '<div class="my_top_message">' . $this->t('You email: %title.', ['%title' => $email])
        )
      );
    }
    else {
      $response->addCommand(
        new HtmlCommand(
          '.result_email',
          '<div class="my_top_message">' . $this->t('Not correct email')
        )
      );
    }
    return $response;
  }

  /**
   * Function to reload page.
   */
  public function setMessage(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

      $response->addCommand(new RedirectCommand('/nekromant512/cats'));
    return $response;
  }

}
