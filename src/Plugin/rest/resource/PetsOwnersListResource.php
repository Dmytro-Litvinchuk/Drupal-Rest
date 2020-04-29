<?php

namespace Drupal\custom_rest\Plugin\rest\resource;

use Drupal\Core\Database\Database;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @RestResource(
 *   id = "pets_owners_list_resource",
 *   label = @Translation("Rest list of pets owners"),
 *   uri_paths = {
 *     "canonical" = "/api/pets_owners",
 *   }
 * )
 */
class PetsOwnersListResource extends ResourceBase {

  /**
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    // By default page 1.
    $page = \Drupal::request()->get('page');
    if ($page <= 0) {
      $page = 1;
    }
    // By default limit 2.
    $limit = \Drupal::request()->get('limit');
    if ($limit <= 0) {
      $limit = 2;
    }
    // Data for using operator LIKE.
    $name = \Drupal::request()->get('name') . '%';
    // Basic query for list pets owners.
    $query = Database::getConnection()
      ->select('pets_owners_storage', 'p')
      ->fields('p')->orderBy('p.pid', 'ASC');
    if (!empty($name)) {
      $query = $query->condition('p.name', $name, 'LIKE');
    }
    $query = $query->range(($page - 1) * $limit, $limit)
      ->execute()->fetchAllAssoc('pid');
    // Object to array.
    foreach ($query as $item) {
      $list[$item->pid] = (array) $item;
    }
    if (!empty($list)) {
      return new ResourceResponse($list);
    }
    throw new NotFoundHttpException(t('Pets owners list ist empty, please try again later...'));
  }

}
