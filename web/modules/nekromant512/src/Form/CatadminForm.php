<?php

namespace Drupal\nekromant512\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Contains \Drupal\nekromant512\Form\CatadminForm.
 *
 * @file
 */

/**
 * Provides an Cat form.
 */
class CatadminForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cat_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $connection = \Drupal::service('database');
    $query = $connection->select('nekromant512', 'a');
    $query->fields('a', ['name', 'mail', 'created', 'image', 'id'])->orderBy('created', 'desc');
    $info = $query->execute()->fetchAll();
    $info = json_decode(json_encode($info),TRUE);
    $headers = [
      t('Cat'),
      t('Image'),
      t('Mail'),
      t('Time'),
      t('Delete'),
      t('Edit'),
    ];
    $rows = [];
    foreach ($info as &$value) {
      $fid = $value['image'];
      $id = $value['id'];
      $name = $value['name'];
      $mail = $value['mail'];
      $created = $value['created'];
      array_splice($value, 0, 5);
      $renderer = \Drupal::service('renderer');
      $file = File::load($fid);
      $img = [
        '#type' => 'image',
        '#theme' => 'image_style',
        '#style_name' => 'thumbnail',
        '#uri' => $file->getFileUri(),
      ];
      $delete = [
        '#type' => 'link',
        '#url' => Url::fromUserInput("/nekromant512/catsDel/$id"),
        '#title' => $this->t('Delete'),
        '#attributes' => [
          'data-dialog-type' => ['modal'],
          'class' => ['button', 'use-ajax'],
        ],
      ];
      $edit = [
        '#type' => 'link',
        '#url' => Url::fromUserInput("/admin/nekromant512/catsChange/$id"),
        '#title' => $this->t('Edit'),
        '#attributes' => ['class' => ['button']],
      ];
      $newId = [
        '#type' => 'hidden',
        '#value' => $id,
      ];
      $value[0] = $name;
      $value[1] = $renderer->render($img);
      $value[2] = $mail;
      $value[3] = $created;
      $value[4] = $renderer->render($delete);
      $value[5] = $renderer->render($edit);
      $value[6] = $newId;
      array_push($rows, $value);
    }
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $headers,
      '#options' => $rows,
      '#empty' => t('No entries available.'),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete selected'),
      '#description' => $this->t('Submit, #type = submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $value = $form['table']['#value'];
    $connection = \Drupal::service('database');
    foreach ($value as $key => $val) {
      $result = $connection->delete('nekromant512');
      $result->condition('id', $form['table']['#options'][$key][6]["#value"]);
      $result->execute();
    }
  }

}
