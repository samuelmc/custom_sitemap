<?php

/*
 * Created by Samuel Moncarey
 * 3/12/2015
 */

namespace Drupal\custom_sitemap\Form;


use Doctrine\Common\Util\Debug;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\custom_sitemap\Customsitemap;
use Drupal\custom_sitemap\SitemapGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomLinkForm
 * @package Drupal\custom_sitemap\Form
 */
class CustomLinkForm extends ConfigFormBase {

  /** @var PathValidatorInterface */
  protected $path_validator;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(\Drupal\Core\Config\ConfigFactoryInterface $config_factory, PathValidatorInterface $path_validator) {
    $this->path_validator = $path_validator;
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('path.validator')
    );
  }


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
    return 'custom_sitemap_link_form';
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

    $sitemap = new Customsitemap();
    $custom_links = $sitemap->get_custom_links();

    $form['#title'] = $this->t('Add new custom link');

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Machine name'),
      '#description' => $this->t('A unique machine-readable name for this content type. It must only contain lowercase letters, numbers, and underscores.'),
      '#required' => TRUE
    );

    $form['path'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#required' => TRUE,
      '#default_value' => ($name !== null && array_key_exists($name, $custom_links) ? $custom_links[$name]['path'] : null)
    );
    $form['index'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Include in sitemap'),
      '#default_value' => ($name !== null && array_key_exists($name, $custom_links) && $custom_links[$name]['index'])
    );
    $form['priority'] = array(
      '#type' => 'select',
      '#title' => $this->t('Priority'),
      '#options' => SitemapGenerator::get_priority_select_values(),
      '#default_value' => ($name !== null && array_key_exists($name, $custom_links) ? $custom_links[$name]['priority'] : SitemapGenerator::PRIORITY_DEFAULT)
    );

    $form = parent::buildForm($form, $form_state);

    $form['actions']['submit']['#value'] = $this->t('Save link');

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $sitemap = new Customsitemap();
    $custom_links = $sitemap->get_custom_links();

    $name = &$form_state->getValue('name');
    $path = &$form_state->getValue('path');

    if ($form_state->getFormObject()->getFormId() == 'custom_sitemap_add_link_form' && array_key_exists($name, $custom_links)) {
      $form_state->setErrorByName('name', $this->t('The machine name must be unique, \'%name\' allready exists.', array('%name' => $name)));
    }

    if (!preg_match('/^[a-z0-9_]+$/',$name)) {
      $form_state->setErrorByName('name', $this->t('The machine name must only contain lowercase letters, numbers, and underscores.'));
    }

    if ($path[0] !== '/') {
      $form_state->setErrorByName('path', 'The path has to start with a slash.');
    }

    if (!$this->path_validator->isValid(trim($path, '/'))) {
      $form_state->setErrorByName('path', t("The path '@link_path' is either invalid or you do not have access to it.", array('@link_path' => $path)));
    }

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

    $sitemap = new Customsitemap();
    $custom_links = $sitemap->get_custom_links();

    $form_state->cleanValues();

    $custom_links[$form_state->getValue('name')] = array(
      'path' => $form_state->getValue('path'),
      'index' => (bool) $form_state->getValue('index'),
      'priority' => (float) $form_state->getValue('priority')
    );

    $sitemap->save_custom_links($custom_links);
    $sitemap->generate_all_sitemaps();

    if ($form_state->getFormObject()->getFormId() == 'custom_sitemap_add_link_form') {
      drupal_set_message($this->t('The custom link has been added to the sitemap.'));
    }
    else {
      drupal_set_message($this->t('The custom link has been saved.'));
    }

    $form_state->setRedirect('custom_sitemap.overview');

  }

}