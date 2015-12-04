<?php
/**
 * @file
 * Contains \Drupal\custom_sitemap\Controller\CustomsitemapController.
 */

namespace Drupal\custom_sitemap\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
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

  public function adminOverview() {

    $sitemap = new Customsitemap();
    $custom_links = $sitemap->get_custom_links();

    $build = array();

    $build['custom_links_table'] = array(
      '#type' => 'table',
      '#header' => array(
        $this->t('Name'),
        $this->t('Path'),
        $this->t('Included in sitemap'),
        $this->t('Priority'),
        $this->t('Operations')
      ),
      '#rows' => array(),
      '#empty' => $this->t('No custom links added to the sitemap.'),
    );

    foreach ($custom_links as $name => $custom_link) {
      $build['custom_links_table']['#rows'][] = array(
        'data' => array(
          'name' => $name,
          'path' => $custom_link['path'],
          'index' => ($custom_link['index'] ? $this->t('yes') : $this->t('No')),
          'priority' => $custom_link['priority'],
          'operations' => array(
            'data' => array(
              '#type' => 'operations',
              '#links' => array(
                'edit' => array(
                  'title' => $this->t('Edit'),
                  'url' => Url::fromRoute('custom_sitemap.edit.form', array('name'=>$name)),
                ),
                'delete' => array(
                  'title' => $this->t('Delete'),
                  'url' => Url::fromRoute('custom_sitemap.delete.form', array('name'=>$name)),
                ),
              ),
            ),
          )
        )
      );
    }

    $build['custom_links_pager'] = array('#type' => 'pager');

    return $build;

  }


  public function addCustomLink() {

    return array();

  }

}
