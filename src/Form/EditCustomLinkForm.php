<?php

/*
 * Created by Samuel Moncarey
 * 3/12/2015
 */

namespace Drupal\custom_sitemap\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class EditCustomLinkForm extends CustomLinkForm {

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
  public function buildForm(array $form, FormStateInterface $form_state, $name = null) {
    $form = parent::buildForm($form, $form_state, $name);

    $form['#title'] = $this->t('Edit %name link', array('%name' => $name));
    $form['name'] = array(
      '#type' => 'value',
      '#value' => $name,
    );
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#submit' => array('::deleteSubmit'),
    );

    return $form;
  }

  /**
   * Submits the delete form.
   */
  public function deleteSubmit(array &$form, FormStateInterface $form_state) {
    $url = new Url('custom_sitemap.delete.form', array(
      'name' => $form_state->getValue('name'),
    ));

    if ($this->getRequest()->query->has('destination')) {
      $url->setOption('query', $this->getDestinationArray());
      $this->getRequest()->query->remove('destination');
    }

    $form_state->setRedirectUrl($url);
  }

}