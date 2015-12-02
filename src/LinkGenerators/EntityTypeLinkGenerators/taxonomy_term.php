<?php
/**
 * @file
 * Contains \Drupal\custom_sitemap\LinkGenerators\EntityTypeLinkGenerators\taxonomy_term.
 *
 * Plugin for taxonomy term entity link generation.
 * See \Drupal\custom_sitemap\LinkGenerators\CustomLinkGenerator\node for more
 * documentation.
 */

namespace Drupal\custom_sitemap\LinkGenerators\EntityTypeLinkGenerators;

use Drupal\custom_sitemap\LinkGenerators\EntityLinkGenerator;
use Drupal\Core\Url;

/**
 * taxonomy_term class.
 */
class taxonomy_term extends EntityLinkGenerator {

  function get_entity_bundle_links($entity_type, $bundle, $languages) {

    $ids = array();
    $query = \Drupal::entityQuery($entity_type)
      ->condition('vid', $bundle);
    $ids += $query->execute();

    $urls = array();
    foreach ($ids as $id => $entity) {
      foreach ($languages as $language) {
        $urls[] = Url::fromRoute("entity.$entity_type.canonical", array('taxonomy_term' => $id), array(
          'language' => $language,
          'absolute' => TRUE
        ))->toString();
      }
    }
    return $urls;
  }
}
