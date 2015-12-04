<?php

/*
 * Created by Samuel Moncarey
 * 4/12/2015
 */

namespace Drupal\custom_sitemap\Form;


use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\custom_sitemap\Customsitemap;

class DeleteCustomLinkForm extends ConfirmFormBase {

  private $link_name;

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['custom_sitemap.settings'];
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'custom_sitemap_add_link_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to remove the custom link "%name" from the sitemap', array('%name' => $this->link_name));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('custom_sitemap.overview');
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
    $this->link_name = $name;
    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $sitemap = \Drupal::service('custom_sitemap.sitemap');
    $custom_links = $sitemap->get_custom_links();

    unset($custom_links[$this->link_name]);

    $sitemap->save_custom_links($custom_links);

    $form_state->setRedirect('custom_sitemap.overview');
  }


}