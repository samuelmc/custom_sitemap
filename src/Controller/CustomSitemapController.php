<?php
/**
 * @file
 * Contains \Drupal\custom_sitemap\Controller\CustomsitemapController.
 */

namespace Drupal\custom_sitemap\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\custom_sitemap\Customsitemap;

/**
 * CustomsitemapController.
 */
class CustomSitemapController extends ControllerBase {

  /**
   * Generates the sitemap.
   */
  public function getSitemap() {

    $sitemap = new Customsitemap();
    $output = $sitemap->get_sitemap();

    // Display sitemap with correct xml header.
    return new Response($output, Response::HTTP_OK, array('content-type' => 'application/xml'));
  }

  public function customLinks() {
    $sitemap = new Customsitemap();



  }


  public function addCustomLink() {

    return array(

    );

  }

}
