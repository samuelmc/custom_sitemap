<?php

/*
 * Created by Samuel Moncarey
 * 3/12/2015
 */

namespace Drupal\custom_sitemap\Form;


class AddCustomLinkForm extends CustomLinkForm {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'custom_sitemap_add_link_form';
  }

}