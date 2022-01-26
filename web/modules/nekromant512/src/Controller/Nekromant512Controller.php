<?php

namespace Drupal\nekromant512\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;

/**
 * Provides route responses for the nekromant512 module.
 */
class nekromant512Controller extends ControllerBase {

  /**
   * Return form for cats.
   */
  public function form() {
    $form = \Drupal::formBuilder()->getForm('Drupal\nekromant512\Form\CatForm');
    return $form;
  }

  /**
   * Return delete button.
   */
  public function delete() {
    $formdelete = \Drupal::formBuilder()->getForm('Drupal\nekromant512\Form\CatForm');
    return $formdelete;
  }

  /**
   * Render all cat entries.
   */
  public function report() {
    $connection = \Drupal::service('database');
    $query = $connection->select('nekromant512', 'a');
    $query->fields('a', ['name', 'mail', 'created', 'image', 'id'])->orderBy('created', 'desc');
    $info = $query->execute()->fetchAll();
    $form = $this->form();
    $delete = $this->delete();
    $rows = [];
    foreach ($info as &$value) {
      $file = File::load($value->image);
      $value->image = [
        '#type' => 'image',
        '#theme' => 'image_style',
        '#style_name' => 'large',
        '#uri' => $file->getFileUri(),
      ];
      $value->images = file_url_transform_relative(file_create_url($file->getFileUri()));
      $renderer = \Drupal::service('renderer');
      $value->image = $renderer->render($value->image);
      array_push($rows, $value);
    }
    return [
      '#theme' => 'cat_template',
      '#items' => $rows,
      '#title' => $this->t('Hello! You can add here a photo of your cat.'),
      '#form' => $form,
      '#delete' => $delete,
    ];
  }

}
