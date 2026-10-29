<?php
namespace Drupal\srl_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomRestController extends ControllerBase {

  /**
   * Handles the POST request and creates the node.
   */
  public function display(Request $request) {
    // Get the data from the request.
    $data = json_decode($request->getContent(), TRUE);
    
    // Create the node using the data.
    // You can use the code for programmatically creating a node that I shared in a previous answer.
    
    // Return a response indicating success or failure.
    if ($nodeCreated) {
      return new JsonResponse(['success' => TRUE]);
    }
    else {
      return new JsonResponse(['success' => FALSE], 500);
    }
  }

}
