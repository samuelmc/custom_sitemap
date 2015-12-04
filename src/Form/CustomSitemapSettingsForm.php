<?php

/*
 * Created by Samuel Moncarey
 * 2/12/2015
 */

namespace Drupal\custom_sitemap\Form;


use Doctrine\Common\Util\Debug;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Url;
use Drupal\custom_sitemap\Customsitemap;
use Drupal\custom_sitemap\SitemapGenerator;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Class CustomSitemapSettingsForm
 * @package Drupal\custom_sitemap\Form
 */
class CustomSitemapSettingsForm extends ConfigFormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'custom_sitemap_settings_form';
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

    $sitemap = new Customsitemap();
    $entity_types = $sitemap->get_entity_types();
    $custom_links = $sitemap->get_custom_links();

    $form = array(
      'entity-types' => array(
        '#type' => 'vertical_tabs'
      ),
      'node' => array(
        '#type' => 'details',
        '#title' => $this->t('Content types'),
        '#group' => 'entity-types'
      ),
      'taxonomy_term' => array(
        '#type' => 'details',
        '#title' => $this->t('Vocabularies'),
        '#group' => 'entity-types'
      )
    );

    /** @var NodeType[] $content_types */
    $content_types = NodeType::loadMultiple();
    $this->setEntityTypeDetailForm($form, 'node', $content_types, $entity_types['node']);

    /** @var Vocabulary[] $vocabularies */
    $vocabularies = Vocabulary::loadMultiple();
    $this->setEntityTypeDetailForm($form, 'taxonomy_term', $vocabularies, $entity_types['taxonomy_term']);

    return parent::buildForm($form, $form_state);
  }


  /**
   * @param array $form
   * @param string $entity_type
   * @param Vocabulary[] | NodeType[] $items
   * @param array $config
   */
  private function setEntityTypeDetailForm(&$form, $entity_type, $items, $config) {
    foreach ($items as $machine_name => $item) {
      $form[$entity_type]["{$entity_type}-{$machine_name}"] = array(
        '#type' => 'fieldset',
        '#title' => $item->label(),
        "{$entity_type}-{$machine_name}-index" => array(
          '#type' => 'checkbox',
          '#title' => $this->t('Include in sitemap'),
          '#default_value' => (array_key_exists($machine_name, $config) && $config[$machine_name]['index'] == 1)
        ),
        "{$entity_type}-{$machine_name}-priority" => array(
          '#type' => 'select',
          '#title' => $this->t('Priority'),
          '#options' => SitemapGenerator::get_priority_select_values(),
          '#default_value' => array_key_exists($machine_name, $config) ? $config[$machine_name]['priority'] : SitemapGenerator::PRIORITY_DEFAULT,
        )
      );
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
    $entity_types = $sitemap->get_entity_types();

    $form_state->cleanValues();

    foreach ($form_state->getValues() as $setting => $value) {
      $setting_keys = explode('-', $setting);
      $entity_types[$setting_keys[0]][$setting_keys[1]][$setting_keys[2]] = $value;
    }

    $sitemap->save_entity_types($entity_types);

    $sitemap->generate_all_sitemaps();

    parent::submitForm($form, $form_state);
  }

}