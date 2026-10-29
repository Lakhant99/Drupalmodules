<?php

/**
 * Implements hook_views_data_alter().
 */
function unfpa_global_custom_views_data_alter(array &$data) {
  
  // Issue Programme Country Title filter for internal audit reports ct.
  $data['node_field_data']['programme_country_titles'] = array(
    'title' => t('Programme Country Title'),
    'group' => t('Content'),
    'filter' => array(
      'title' => t('Programme Country Title'),
      'help' => t('Specify a list of titles a content node can have.'),
      'field' => 'nid',
      'id' => 'programme_country_titles'
    ),
  );
  
  $data['node']['date_dropdown_filter'] = [
    'title' => t('Date dropdown filter'),
    'group' => t('Content'),
    'filter' => [
      'title' => t('Date filter'),
      'help' => t('Provides a custom filter for nodes by their date field.'),
      'field' => 'nid',
      'id' => 'date_dropdown_views_filter',
    ],
  ];
  // Issue date filter for internal audit reports ct.
  $data['node']['issue_date_dropdown_filter'] = [
    'title' => t('Issue date dropdown filter'),
    'group' => t('Content'),
    'filter' => [
      'title' => t('Issue date filter'),
      'help' => t('Provides a custom filter for nodes by their date field.'),
      'field' => 'nid',
      'id' => 'issue_date_dropdown_views_filter',
    ],
  ];
}
