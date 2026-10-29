<?php

namespace Drupal\unfpa_global_donation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Path\AliasStorage;
use Drupal\node\Entity\Node;
use Drupal\Core\Cache\Cache;
use Drupal\file\Entity\File;

/**
 * Thank you page class which define successful message.
 */
class ThankYouPage extends ControllerBase {
  /**
   * The Project Page Service.
   *
   * @var \Drupal\Core\Path\AliasStorage
   */
  protected $pathAliasStorage;

  /**
   * The Cache backend manager.
   *
   * @var Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Request Stack Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request, CacheBackendInterface $cache_backend, AliasStorage $path_alias_storage) {
    $this->request = $request;
    $this->cacheBackend = $cache_backend;
    $this->pathAliasStorage = $path_alias_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('cache.default'),
      $container->get('path.alias_storage')
    );
  }

  /**
   * Callback function for thankyou page.
   */
  public function content($alias) {
    if ($alias) {
      // Attach slash to alias and get node id.
      if (is_string($alias)) {
        $alias = '/' . $alias;
      }

      $lang_code = $this->languageManager()->getCurrentLanguage()->getId();
      $node_link = $this->pathAliasStorage->lookupPathSource($alias, $lang_code);

      $data = [];
      if ($node_link) {
        $node_array = explode('/', $node_link);

        $node_id = end($node_array);
        $node = $this->entityTypeManager()->getStorage('node')->load($node_id);

        if ($node instanceof Node) {
          // If node is translated, get translated node.
          if ($node->get('langcode')->value != $lang_code && $node->hasTranslation($lang_code)) {
            $node = $node->getTranslation($lang_code);
          }

          // Share cart title.
          if ($share_card_title = $node->get('field_share_card_title')->value) {
            $data['share_card_title'] = $share_card_title;
          }
          else {
            $data['share_card_title'] = $node->getTitle();
          }

          // Share card image.
          $fieldName = 'field_share_card_image';
          if ($node->hasField($fieldName)) {
            $field = $node->{$fieldName};
            if ($field && $field->target_id) {
              $file = File::load($field->target_id);
              if ($file) {
                $image_uri = $file->getFileUri();
                $data['share_card_image'] = file_create_url($image_uri);
                $data['is_share'] = TRUE;
              }
            }
          }
          else {
            $ct_donate_banner = $node->get('field_donate_banner')->getValue();
            foreach ($ct_donate_banner as $db_fields) {
              $db_paragraph = $this->entityTypeManager()->getStorage('paragraph')->load($db_fields['target_id']);

              if ($db_paragraph->get('field_show_as_banner')->value == 1) {
                $effect_type = $db_paragraph->get('field_effect_type')->value;

                if ($effect_type == 'withEffect') {
                  // Desktop Banner Image.
                  $banner_desktop = $db_paragraph->get('field_banner_image_desktop')->entity->getFileUri();
                  if ($banner_desktop) {
                    $data['share_card_image'] = file_create_url($banner_desktop);
                  }
                }
                if ($effect_type == 'withoutHeader') {
                  // Desktop Banner Image.
                  $banner_desktop = $db_paragraph->get('field_banner_image_desktop')->entity->getFileUri();
                  if ($banner_desktop) {
                    $data['share_card_image'] = file_create_url($banner_desktop);
                  }
                }
                if ($effect_type == 'withHeader') {
                  // Desktop Banner Image.
                  $banner_desktop = $db_paragraph->get('field_half_banner_image_desktop')->entity->getFileUri();
                  if ($banner_desktop) {
                    $data['share_card_image'] = file_create_url($banner_desktop);
                  }
                }
                else {
                  // Large Banner Image.
                  $banner_large = $db_paragraph->get('field_banner_image_large')->entity->getFileUri();
                  if ($banner_large) {
                    $data['share_card_image'] = file_create_url($banner_large);
                  }
                }
              }
            }
            $data['is_share'] = FALSE;
          }

          $data['nid'] = $node->id();
          $data['node_alias'] = $alias;
          $data['node_title'] = $node->getTitle();
          $data['description'] = $node->get('field_description')->value;
          $data['share_description'] = $node->get('field_share_desc')->value;
          $cache_list = [
            'keys' => ['entity_view', 'node', $node->id()],
            'tags' => $node->getCacheTags(),
            'max-age' => Cache::PERMANENT,
          ];

        }
        else {
          throw new NotFoundHttpException();
        }
      }
      else {
        throw new NotFoundHttpException();
      }

      // Remove donation_thank_you variable from session.
      $this->request->getCurrentRequest()->getSession()->remove('donation_thankyou');

    }
    else {
      throw new NotFoundHttpException();
    }

    return [
      '#theme' => 'donation_thank_you',
      '#data' => $data,
      '#attached' => [
        'library' => [
          'unfpa_global_donation/donate_page',
          'unfpa_global_donation/thankyou',
          'unfpa_global/parallax-libraries',
        ],
        'drupalSettings' => [
          'thankyou_image' => $data['share_card_image'],
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Checks access for thankyou page.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkAccess(AccountInterface $account, $alias) {
    // Cant's use DependencyInjection here...
    // AccessResult not available as service.
    $is_valid = FALSE;
    if ($this->request->getCurrentRequest()->hasSession()) {
      if ($this->request->getCurrentRequest()->getSession()->get('donation_thankyou')) {
        $is_valid = TRUE;
      }
    }
    return AccessResult::allowedIf($account->hasPermission('access content') && $is_valid);
  }

}
