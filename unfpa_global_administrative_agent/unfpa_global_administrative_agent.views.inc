<?php

/**
 * Implements hook_views_data_alter().
 */

function unfpa_global_administrative_agent_views_data_alter(array &$data) {
  // AA Finance accounting date.
  $data['node']['accounting_aa_date_dropdown_views_filter'] = [
    'title' => t('AA Finance Accounting Date Filter'),
    'group' => t('Content'),
    'filter' => [
      'title' => t('AA Finance Accounting Date Filter'),
      'help' => t('Provides a custom date filter'),
      'field' => 'nid',
      'id' => 'accounting_aa_date_dropdown_views_filter',
    ],
  ];
}
