<?php

namespace Drupal\imce\Entity;

/**
 * A synthetic profile that merges several real Imce profiles into one.
 *
 * Upstream Imce picks a single profile per user (the first role match, walking
 * roles most permissive first). SXK members often belong to several groups, and
 * each group has its own profile and folders, so a single profile means a member
 * only ever sees one group's folder.
 *
 * This class merges them on the principle that **the most permissive wins**:
 *
 * - folders are the union of every profile's folders, de-duplicated by path;
 * - permissions on a shared path are unioned, honouring the `all` wildcard;
 * - numeric limits take the most generous value, where 0 means "no limit";
 * - allowed extensions are unioned;
 * - anything else takes the first profile's value, since Imce::userProfile()
 *   supplies profiles in most-permissive-first order.
 *
 * @see \Drupal\imce\Imce::userProfile()
 * @see \Drupal\imce\Imce::permissionInFolderConf()
 */
class AggregatedImceProfile implements ImceProfileInterface {

  /**
   * The merged profiles, keyed by profile id so each profile counts once.
   *
   * Several roles routinely map to the same profile; without de-duplication the
   * same folders are emitted repeatedly.
   *
   * @var \Drupal\imce\Entity\ImceProfileInterface[]
   */
  private array $profiles = [];

  /**
   * Memoised result of mergedConf().
   *
   * @var array|null
   */
  private ?array $merged = NULL;

  /**
   * Numeric limits where larger is more permissive and 0 means no limit.
   */
  private const LIMIT_KEYS = ['maxsize', 'quota', 'maxwidth', 'maxheight'];

  private function __construct(array $profiles = []) {
    foreach ($profiles as $profile) {
      if ($profile instanceof ImceProfileInterface) {
        $this->add($profile);
      }
    }
  }

  /**
   * Adds a profile to the aggregate.
   */
  public function add(ImceProfileInterface $profile): void {
    // Keyed by id: de-duplicates profiles shared by several of the user's roles.
    // First occurrence wins, preserving most-permissive-first ordering.
    $id = (string) $profile->id();
    if (!isset($this->profiles[$id])) {
      $this->profiles[$id] = $profile;
      $this->merged = NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConf($key = NULL, $default = NULL) {
    $merged = $this->mergedConf();
    if ($key === NULL) {
      return $merged;
    }
    // A keyed lookup must return that key's merged value. Returning the whole
    // merged array would hand callers an array where a scalar is expected.
    return $merged[$key] ?? $default;
  }

  /**
   * Merges the configuration of every profile, most permissive winning.
   */
  private function mergedConf(): array {
    if ($this->merged !== NULL) {
      return $this->merged;
    }

    $confs = [];
    foreach ($this->profiles as $profile) {
      $conf = $profile->getConf();
      if (is_array($conf)) {
        $confs[] = $conf;
      }
    }

    $merged = [];
    foreach ($confs as $conf) {
      foreach ($conf as $name => $value) {
        if ($name === 'folders') {
          continue;
        }
        if (!array_key_exists($name, $merged)) {
          $merged[$name] = $value;
          continue;
        }
        $merged[$name] = self::mergeValue($name, $merged[$name], $value);
      }
    }
    $merged['folders'] = self::mergeFolders($confs);

    return $this->merged = $merged;
  }

  /**
   * Merges one configuration value, taking the more permissive of the two.
   */
  private static function mergeValue(string $name, $current, $next) {
    if (in_array($name, self::LIMIT_KEYS, TRUE)) {
      // 0 / empty means "no limit imposed by the profile" (see
      // Imce::processUserConf, which then falls back to the PHP limit), so it is
      // the most permissive value and must not lose to a concrete number.
      if (empty($current) || empty($next)) {
        return 0;
      }
      return max($current, $next);
    }

    if ($name === 'extensions') {
      $tokens = array_filter(
        array_unique(array_merge(
          preg_split('/\s+/', trim((string) $current)) ?: [],
          preg_split('/\s+/', trim((string) $next)) ?: []
        )),
        static fn($token) => $token !== ''
      );
      sort($tokens);
      return implode(' ', $tokens);
    }

    // Nothing else has a permissiveness ordering (thumbnail styles, replace
    // behaviour, …) — keep the first, i.e. the most permissive profile's choice.
    return $current;
  }

  /**
   * Unions the folders of every profile, de-duplicating by path.
   */
  private static function mergeFolders(array $confs): array {
    $by_path = [];
    foreach ($confs as $conf) {
      foreach ($conf['folders'] ?? [] as $folder) {
        if (!isset($folder['path'])) {
          continue;
        }
        $path = $folder['path'];
        if (!isset($by_path[$path])) {
          $by_path[$path] = $folder;
          continue;
        }
        // Same folder reachable through several profiles: grant the union.
        // Imce::processUserFolders() keys folders by path, so without this the
        // last-seen (least permissive) copy would silently win.
        $by_path[$path]['permissions'] = self::mergePermissions(
          $by_path[$path]['permissions'] ?? [],
          $folder['permissions'] ?? []
        );
      }
    }
    // Re-index: processUserFolders() iterates values and reads ['path'].
    return array_values($by_path);
  }

  /**
   * Unions two permission sets, honouring the `all` wildcard.
   *
   * Imce::permissionInFolderConf() reads an explicit key when present and only
   * falls back to `all` otherwise — so `['all' => TRUE, 'delete_files' => FALSE]`
   * denies deletion. A union therefore has to resolve each side's *effective*
   * value before OR-ing, and must keep an explicit FALSE where the wildcard
   * would otherwise re-grant something neither side allowed.
   */
  private static function mergePermissions(array $a, array $b): array {
    $all_a = !empty($a['all']);
    $all_b = !empty($b['all']);

    $names = array_diff(
      array_unique(array_merge(array_keys($a), array_keys($b))),
      ['all']
    );

    $merged = [];
    if ($all_a || $all_b) {
      $merged['all'] = TRUE;
    }
    foreach ($names as $name) {
      $effective_a = array_key_exists($name, $a) ? (bool) $a[$name] : $all_a;
      $effective_b = array_key_exists($name, $b) ? (bool) $b[$name] : $all_b;
      if ($effective_a || $effective_b) {
        $merged[$name] = TRUE;
      }
      elseif (!empty($merged['all'])) {
        // Neither side grants it, but the wildcard would. Deny explicitly.
        $merged[$name] = FALSE;
      }
    }
    return $merged;
  }

  /**
   * Creates an aggregate from a list of profiles.
   *
   * @param \Drupal\imce\Entity\ImceProfileInterface[] $profiles
   *   Profiles ordered most permissive first.
   */
  public static function createAggregatedImceProfile(array $profiles = []): AggregatedImceProfile {
    return new self($profiles);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return implode(',', array_keys($this->profiles));
  }

}
