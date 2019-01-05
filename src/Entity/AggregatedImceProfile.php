<?php
namespace Drupal\imce\Entity;

class AggregatedImceProfile implements ImceProfileInterface {

  /**
   * @var array
   */
  private $profiles;

  /**
   * AggregatedImceProfile constructor.
   *
   * @param array $profiles
   */
  private function __construct(array $profiles = []) {
    $this->profiles = array_filter($profiles, 'isset');
  }


  public function add(ImceProfileInterface $profile): void {
    $this->profiles[] = $profile;
  }

  public function getConf($key = NULL, $default = NULL) {
    // Map the original profile configurations
    $confs = array_map(function (ImceProfileInterface $profile) use ($key, $default) {
      return $profile->getConf($key, $default);
    }, $this->profiles);

    // The folders are what we need to merge to give users with several profiles access
    // to all profiles folders. We copy over the folder configurations and
    // merge them in to the resulting folders configuration.
    $folders = [];
    foreach ($confs as $current) {
        $folders[] = $current['folders'];
    }
    $conf = array_reduce($confs, 'array_merge', []);
    $conf['folders'] = array_merge(...$folders);

    return $conf;
  }

  public static function createAggregatedImceProfile(array $profiles = []): AggregatedImceProfile {
    return new self($profiles);
  }
}