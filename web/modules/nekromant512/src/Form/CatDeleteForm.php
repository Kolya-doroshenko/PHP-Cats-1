<?php

namespace Drupal\nekromant512\Form;

use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Url;

/**
 * Contains \Drupal\nekromant512\Form\CatDeleteForm.
 *
 * @file
 */

/**
 * Provides an Cat form.
 */
class CatDeleteForm extends FormBase {

  /**
   * Contain slug id to delete cat entry.
   *
   * @var ctid
   */
  protected $ctid = 0;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cat_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cid = NULL) {
    $form['delete'] = [
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#ajax' => [
        'callback' => '::ajaxForm',
        'event' => 'click',
      ],
    ];
    $this->ctid = $cid;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $connection = \Drupal::service('database');
    $result = $connection->delete('nekromant512');
    $result->condition('id', $this->ctid);
    $result->execute();
  }

  /**
   * Function to reload page.
   */
  public function ajaxForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $currentURL = Url::fromRoute('nekromant512.cats_page');
    $response->addCommand(new RedirectCommand($currentURL->toString()));
    return $response;
  }

}
