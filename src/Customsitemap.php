<?php
/**
 * @file
 * Contains \Drupal\custom_sitemap\Customsitemap.
 */

namespace Drupal\custom_sitemap;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Config\Config;

/**
 * Customsitemap class.
 */
class Customsitemap {

  const SITEMAP_PLUGIN_PATH = 'src/LinkGenerators/EntityTypeLinkGenerators';

  const CONFIG_SETTINGS = 'custom_sitemap.settings';

  /** @var Connection */
  private $db;

  /** @var Config */
  private $config;

  /** @var ConfigFactoryInterface */
  private $config_factory;

  /** @var null|string */
  private $sitemap;

  /** @var LanguageInterface */
  private $language;

  function __construct(Connection $connection, ConfigFactoryInterface $config_factory) {
    $this->set_language();
    $this->db = $connection;
    $this->config_factory = $config_factory;
    $this->config = $this->config_factory->get(self::CONFIG_SETTINGS);
    $this->sitemap = $this->get_sitemap_from_db();
  }

  /**
   * @param FormStateInterface $form_state
   * @return object | bool
   */
  public static function get_form_entity($form_state) {
    if (!is_null($form_state->getFormObject()) && method_exists($form_state->getFormObject(), 'getEntity')) {
      $entity = $form_state->getFormObject()->getEntity();
      return $entity;
    }
    return FALSE;
  }

  public static function get_entity_type_name($entity) {
    if (method_exists($entity, 'getEntityType')) {
      return $entity->getEntityType()->getBundleOf();
    }
    return FALSE;
  }

  public static function get_plugin_path($entity_type_name) {
    $class_path = drupal_get_path('module', 'custom_sitemap') . '/' . self::SITEMAP_PLUGIN_PATH . '/' . $entity_type_name . '.php';
    if (file_exists($class_path)) {
      return $class_path;
    }
    return FALSE;
  }

  private function set_language($language = null) {
    $this->language = $language === null ? \Drupal::languageManager()->getCurrentLanguage() : $language;
  }

  private function get_sitemap_from_db() {
    /** @var SelectInterface $query */
    $query = $this->db->select('custom_sitemap', 's')
      ->fields('s', array('sitemap_string'))
      ->condition('language_code', $this->language->getId());
    $result = $query->execute()->fetchAll();
    return (!empty($result[0]->sitemap_string)) ? $result[0]->sitemap_string : NULL;
  }

  public function save_entity_types($entity_types) {
    $this->save_config('entity_types', $entity_types);
  }

  public function save_custom_links($custom_links) {
    $this->save_config('custom', $custom_links);
  }

  private function save_config($key, $value) {
    $this->config_factory->getEditable(self::CONFIG_SETTINGS)->set($key, $value)->save();
  }

  public function get_sitemap() {
    if (empty($this->sitemap)) {
      $this->generate_sitemap();
    }
    return $this->sitemap;
  }

  private function generate_sitemap() {
    $generator = new SitemapGenerator();
    $generator->set_sitemap_lang($this->language);
    $generator->set_custom_links($this->config->get('custom'));
    $generator->set_entity_types($this->config->get('entity_types'));
    $this->sitemap = $generator->generate_sitemap();
    $this->save_sitemap();
  }

  public function generate_all_sitemaps() {
    $generator = new SitemapGenerator();
    $generator->set_custom_links($this->config->get('custom'));
    $generator->set_entity_types($this->config->get('entity_types'));
    foreach(\Drupal::languageManager()->getLanguages() as $language) {
      $generator->set_sitemap_lang($language);
      $this->language = $language;
      $this->sitemap = $generator->generate_sitemap();
      $this->save_sitemap();
    }
  }

  private function save_sitemap() {
    $this->db->upsert('custom_sitemap')
      ->key(array('language_code', $this->language->getId()))
      ->fields(array(
        'language_code' => $this->language->getId(),
        'sitemap_string' => $this->sitemap,
      ))
      ->execute();
  }

  public function get_entity_types() {
    return $this->config->get('entity_types');
  }

  public function get_custom_links() {
    return $this->config->get('custom');
  }
}
