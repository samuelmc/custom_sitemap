<?php

/*
 * Created by Samuel Moncarey
 * 3/12/2015
 */

namespace Drupal\custom_sitemap\Form;


use Drupal\Core\Form\FormStateInterface;

class AddCustomLinkForm extends CustomLinkForm {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'custom_sitemap_edit_link_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);



  }


}