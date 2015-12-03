<?php
/**
 * @file
 * Contains \Drupal\custom_sitemap\LinkGenerators\CustomLinkGenerator.
 *
 * Generates custom sitemap links provided by the user.
 */

namespace Drupal\custom_sitemap\LinkGenerators;

use Doctrine\Common\Util\Debug;
use Drupal\Core\Url;
use Drupal\custom_sitemap\SitemapGenerator;

/**
 * CustomLinkGenerator class.
 */
class CustomLinkGenerator {

  public function get_custom_links($custom_paths, $language) {
    $links = array();
    foreach($custom_paths as $custom_path) {
      if ($custom_path['index']) {
        $links[] = SitemapGenerator::add_xml_link_markup(Url::fromUserInput($custom_path['path'], array(
          'language' => $language,
          'absolute' => TRUE
        ))->toString(), $custom_path['priority']);
      }
    }
    return $links;
  }
}
