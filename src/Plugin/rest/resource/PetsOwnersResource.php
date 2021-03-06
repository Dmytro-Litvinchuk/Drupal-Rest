<?php

namespace Drupal\custom_rest\Plugin\rest\resource;

use Drupal\Core\Database\Database;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @RestResource(
 *   id = "pets_owners_resource",
 *   label = @Translation("Endpoint GET"),
 *   uri_paths = {
 *     "canonical" = "/api/pets_owners/{pid}",
 *     "https://www.drupal.org/link-relations/create" =
 *   "/api/pets_owners/{pid}",
 *   }
 * )
 */
class PetsOwnersResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function get($pid = NULL) {
    if ($pid > 0) {
      $query = Database::getConnection()
        ->select('pets_owners_storage', 'p')
        ->condition('p.pid', $pid)
        ->fields('p')
        ->execute()
        ->fetchAssoc();
      if (!empty($query)) {
        return new ResourceResponse($query);
      }
      throw new NotFoundHttpException(t('Pets owner with PID :pid was not found', [':pid' => $pid]));
    }
    throw new BadRequestHttpException(t('No entry PID was provided'));
  }

  /**
   * Responds to POST requests.
   *
   * @param null $pid
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function post($pid = NULL, $data) {
    // Array of fields from DB.
    $fields = [
      'prefix' => 'prefix',
      'name' => 'name',
      'gender' => 'gender',
      'age' => 'age',
      'father' => 'father',
      'mother' => 'mother',
      'pets_name' => 'pets_mame',
      'email' => 'email',
    ];
    // Only value like DB, $data get from headers.
    $value = array_intersect_key($data, $fields);
    if ($pid > 0 && !empty($value)) {
      try {
        $query = \Drupal::database();
        $update = $query->update('pets_owners_storage')
          ->fields($value)
          ->condition('pid', $pid)
          ->execute();
        if ($update == TRUE) {
          return new ResourceResponse('That pid was updated in DB');
        }
      }
      catch (Exception $e) {
        throw new HttpException(500, 'Internal Server Error', $e);
      }
      throw new NotFoundHttpException(t('Pets owner with PID :pid was not found', [':pid' => $pid]));
    }
    throw new BadRequestHttpException(t('No entry PID or query parameters was provided'));
  }

  /**
   * Responds to DELETE requests.
   *
   * @param null $pid
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function delete($pid = NULL) {
    if ($pid > 0) {
      try {
        $query = \Drupal::database();
        $result = $query->delete('pets_owners_storage')
          ->condition('pid', $pid)
          ->execute();
        // Check success of delete.
        if ($result == TRUE) {
          // Response empty because method delete.
          return new ModifiedResourceResponse(NULL, 204);
        }
        else {
          return new ModifiedResourceResponse(NULL, 400);
        }
      }
      catch (Exception $e) {
        throw new HttpException(500, 'Internal Server Error', $e);
      }
    }
    return new ModifiedResourceResponse(NULL, 400);
  }

}
