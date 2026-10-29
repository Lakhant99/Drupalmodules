<?php

namespace Drupal\unfpa_global_custom_funds\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Database\Connection;

/**
 *
 * @Block(
 *   id = "unfpa_global_custom_incoming_funds",
 *   admin_label = @Translation("Incoming Funds Block"),
 *   category = @Translation("Custom")
 * )
 */
class IncomingFundsBlock extends BlockBase implements ContainerFactoryPluginInterface {
  
  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;
  
  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $query = $this->connection->select('incoming_funds', 'i');
    $query->fields('i', array('year'));
    $query->orderBy('i.year', 'DESC');
    $result = $query->distinct()->execute()->fetchAll();
    return [
      '#theme' => 'unfpa_global_custom_incoming_funds',
      '#data' => $result,
      '#attached' => [
        'library' => [          
          'unfpa_global_custom_funds/unfpa_global_custom_funds.update_chart',
          'unfpa_global_custom_funds/unfpa_global_custom_funds.custom_incoming_funds',
        ],
      ],
    ];
  }

}
