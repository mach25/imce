<?php
namespace Drupal\imce\Entity;

interface ImceProfileInterface {

  /**
   * Returns configuration options.
   *
   * @param string|null $key
   * @param mixed|null $default
   */
  public function getConf($key = NULL, $default = NULL);
}