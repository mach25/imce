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

  /**
   * Gets the identifier.
   *
   * @return string|int|null
   *   The entity identifier, or NULL if the object does not yet have an
   *   identifier.
   */
  public function id();
}