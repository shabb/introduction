<?php

namespace Drupal\intro_guide\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class IntroGuideController extends ControllerBase {

  /**
   * Display the Markup.
   */
  public function content() {
    return array(
      '#type' => 'markup',
      '#markup' => $this->t('Hello World!'),
    );
  }

  /**
   * Auto complete text box for nids.
   */
  public function autocomplete(request $request) {
    $matches = array();
    $string = $request->query->get('q');
    if ($string) {
      $matches = array();
      $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('title', '%' . db_like($string) . '%', 'LIKE');
      $nids = $query->execute();
      $result = entity_load_multiple('node', $nids);
      foreach ($result as $row) {
        $matches[] = ['value' => $row->nid->value, 'label' => $row->title->value];
      }
    }
    return new JsonResponse($matches);
  }
}