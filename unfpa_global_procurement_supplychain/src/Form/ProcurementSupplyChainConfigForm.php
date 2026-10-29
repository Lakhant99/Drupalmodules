<?php

namespace Drupal\unfpa_global_procurement_supplychain\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\file\FileUsage\DatabaseFileUsageBackend;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Procurement Supply Chain Configuration Form.
 */
class ProcurementSupplyChainConfigForm extends ConfigFormBase {
      
    
    /**
   * The form settings.
   */
  protected $settings;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Language Manager.
   */
  protected $languageManager;

  /**
   * The File Usage Service.
   */
  protected $fileUsage;

  /**
   * The current language.
   */
  protected $userLanguage;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LanguageManager $language_manager, DatabaseFileUsageBackend $file_usage) {
    $this->settings = 'unfpa_global_procurement_supplychain.settings';
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->fileUsage = $file_usage;

    // Get User language.
    $this->userLanguage = $this->languageManager->getCurrentLanguage()->getId();
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'), $container->get('entity_type.manager'), $container->get('language_manager'), $container->get('file.usage')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      $this->settings,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'procurement_supplychain_config_form';
  }

  /**
   * Get Language List.
   */
  public function getLanguageList() {
    $languages = $this->languageManager->getLanguages();

    $languageList = [];
    foreach ($languages as $id => $obj) {
      $languageList[$id] = $obj->getName();
    }

    // Removing certain languages.
    unset($languageList['zh-hans']);
    unset($languageList['ar']);
    unset($languageList['ha']);
    unset($languageList['pcm']);
    return $languageList;
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    // Get Language List.
    $languageList = $this->getLanguageList();

    // Get default values.
    $config = $this->config('unfpa_global_procurement_supplychain.settings')->get("config_$this->userLanguage");
    
    //Banner Image Details Details for What We Do Tab fields
    $whatWeDoBannerText = isset($config["what_we_do_banner_text"]) ? $config["what_we_do_banner_text"] : '';
    $whatWeDoBannerImage = isset($config['what_we_do_banner_image']) ? $config['what_we_do_banner_image'] : '';
    $whatWeDoMobileBannerImage = isset($config['what_we_do_mobile_banner_image']) ? $config['what_we_do_mobile_banner_image'] : '';
    
    //Banner Image Details Details for For governments & Partners Tab fields
    $govtPartBannerText = isset($config["govt_part_banner_text"]) ? $config["govt_part_banner_text"] : '';
    $govtPartBannerImage = isset($config['govt_part_banner_image']) ? $config['govt_part_banner_image'] : '';
    $govtPartMobileBannerImage = isset($config['govt_part_mobile_banner_image']) ? $config['govt_part_mobile_banner_image'] : '';
    
    //Banner Image Details Details for For Suppliers Tab fields
    $suppliersBannerText = isset($config["suppliers_banner_text"]) ? $config["suppliers_banner_text"] : '';
    $suppliersBannerImage = isset($config['suppliers_banner_image']) ? $config['suppliers_banner_image'] : '';
    $suppliersMobileBannerImage = isset($config['suppliers_mobile_banner_image']) ? $config['suppliers_mobile_banner_image'] : '';
    
    //Banner Image Details Details for Products & Tools Tab fields
    $productsBannerText = isset($config["prod_banner_text"]) ? $config["prod_banner_text"] : '';
    $productsBannerImage = isset($config['prod_banner_image']) ? $config['prod_banner_image'] : '';
    $productsMobileBannerImage = isset($config['prod_mobile_banner_image']) ? $config['prod_mobile_banner_image'] : '';
    
    //Banner Image Details Details for Resources Tab fields
    $resourcesBannerText = isset($config["res_banner_text"]) ? $config["res_banner_text"] : '';
    $resourcesBannerImage = isset($config['res_banner_image']) ? $config['res_banner_image'] : '';
    $resourcesMobileBannerImage = isset($config['res_mobile_banner_image']) ? $config['res_mobile_banner_image'] : '';
    
    //Banner Image Details Details for My Account Tab fields
    $myAccBannerText = isset($config["my_acc_banner_text"]) ? $config["my_acc_banner_text"] : '';
    $myAccBannerImage = isset($config['my_acc_banner_image']) ? $config['my_acc_banner_image'] : '';
    $myAccMobileBannerImage = isset($config['my_acc_mobile_banner_image']) ? $config['my_acc_mobile_banner_image'] : '';

    $form['#attributes']['enctype'] = "multipart/form-data";
    $form['language_config'] = [
      '#type' => 'details',
      '#title' => t('Language configuration'),
      '#open' => TRUE,
    ];
    $lan = '';
    // Set language list.
    foreach ($languageList as $key => $value) {
      if ($key != 'en') {
        $lan .= '<a href="' . $base_url . '/' . $key . '/admin/unfpa_global_site_config/procurement-supplychain-config" class="lang_redirect">' . $value . '</a>  |  ';
      }
      else {
        $lan .= '<a href="' . $base_url . '/admin/unfpa_global_site_config/procurement-supplychain-config" class="lang_redirect">' . $value . '</a>  |  ';
      }
    }
    $form['language_config']['language'] = [
      '#type' => 'markup',
      '#markup' => $lan,
    ];

    $form['ex_config'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Procurement & Supply Chain Configuration'),
      '#prefix' => '<div id="proc-supply-config-form">',
      '#suffix' => '</div>',
    ];

    //Fields for What We Do Tab begin here
    $form['ex_config']['#tree'] = TRUE;
    $form['ex_config']['what_we_do_banner_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('What We Do Banner Text'),
      '#default_value' => $whatWeDoBannerText,
    ];
    $form['ex_config']['what_we_do_banner_image'] = [
      '#title' => $this->t('What We Do Banner image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed for What We Do banner. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_what_we_do__image',
      '#default_value' => $whatWeDoBannerImage,
    ];
    $form['ex_config']['what_we_do_mobile_banner_image'] = [
      '#title' => $this->t('What We Do Banner mobile image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed What We Do banner in mobile view. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_what_we_do__image',
      '#default_value' => $whatWeDoMobileBannerImage,
    ];
    //Fields for What We Do Tab ends here
    
    
    //Fields for Governments & Partners Tab Begins here
    $form['ex_config']['#tree'] = TRUE;
    $form['ex_config']['govt_part_banner_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Governments & Partners Banner Text'),
      '#default_value' => $govtPartBannerText,
    ];
    $form['ex_config']['govt_part_banner_image'] = [
      '#title' => $this->t('Governments & Partners Banner image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed for Governments & Partners banner. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_govt_partners__image',
      '#default_value' => $govtPartBannerImage,
    ];
    $form['ex_config']['govt_part_mobile_banner_image'] = [
      '#title' => $this->t('Governments & Partners Banner mobile image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed Governments & Partners banner in mobile view. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_govt_partners__image',
      '#default_value' => $govtPartMobileBannerImage,
    ];
    //Fields for Governments & Partners Tab ends here
    
    
    //Fields for Suppliers Tab begins here
    $form['ex_config']['#tree'] = TRUE;
    $form['ex_config']['suppliers_banner_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Suppliers Banner Text'),
      '#default_value' => $suppliersBannerText,
    ];
    $form['ex_config']['suppliers_banner_image'] = [
      '#title' => $this->t('Suppliers Banner image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed for Suppliers banner. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_suppliers__image',
      '#default_value' => $suppliersBannerImage,
    ];
    $form['ex_config']['suppliers_mobile_banner_image'] = [
      '#title' => $this->t('Suppliers Banner mobile image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed Suppliers banner in mobile view. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_suppliers__image',
      '#default_value' => $suppliersMobileBannerImage,
    ];
    //Fields for Suppliers Tab ends here
    
    //Fields for Products & Tools Tab begins here
    $form['ex_config']['#tree'] = TRUE;
    $form['ex_config']['prod_banner_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Products & Tools Banner Text'),
      '#default_value' => $productsBannerText,
    ];
    $form['ex_config']['prod_banner_image'] = [
      '#title' => $this->t('Products & Tools Banner image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed for Products & Tools banner. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_products_tools__image',
      '#default_value' => $productsBannerImage,
    ];
    $form['ex_config']['prod_mobile_banner_image'] = [
      '#title' => $this->t('Products & Tools Banner mobile image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed Products & Tools banner in mobile view. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_products_tools__image',
      '#default_value' => $productsMobileBannerImage,
    ];
    //Fields for Products & Tools Tab ends here
    
    //Fields for Resources Tab begins here
    $form['ex_config']['#tree'] = TRUE;
    $form['ex_config']['res_banner_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Resources Banner Text'),
      '#default_value' => $resourcesBannerText,
    ];
    $form['ex_config']['res_banner_image'] = [
      '#title' => $this->t('Resources Banner image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed for Resources banner. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_resources__image',
      '#default_value' => $resourcesBannerImage,
    ];
    $form['ex_config']['res_mobile_banner_image'] = [
      '#title' => $this->t('Resources Banner mobile image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed Resources banner in mobile view. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_resources__image',
      '#default_value' => $resourcesMobileBannerImage,
    ];
    //Fields for Resources Tab ends here
    
    //Fields for My Account Tab begins here
    $form['ex_config']['#tree'] = TRUE;
    $form['ex_config']['my_acc_banner_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('My Account Banner Text'),
      '#default_value' => $myAccBannerText,
    ];
    $form['ex_config']['my_acc_banner_image'] = [
      '#title' => $this->t('My Account Banner image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed for My Account banner. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_my_acc__image',
      '#default_value' => $myAccBannerImage,
    ];
    $form['ex_config']['my_acc_mobile_banner_image'] = [
      '#title' => $this->t('My Account Banner mobile image'),
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed My Account banner in mobile view. Uploads limited to .png .gif .jpg .jpeg extensions'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_location' => 'public://proc_my_acc__image',
      '#default_value' => $myAccMobileBannerImage,
    ];
    //Fields for My Account Tab ends here
    
    return parent::buildForm($form, $form_state);
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $userLanguage = $this->userLanguage;
    // Get the header counter.
    $submittedValues["language"] = $userLanguage;
    //Submitted values for What We Do fields begins here
    $submittedValues["what_we_do_banner_text"] = $values['ex_config']['what_we_do_banner_text'];
    //Submitted values for What We Do fields ends here
    //Submitted values for Governments & Partners fields begins here
    $submittedValues["govt_part_banner_text"] = $values['ex_config']['govt_part_banner_text'];
    //Submitted values for Governments & Partners fields ends here
    //Submitted values for Suppliers fields begins here
    $submittedValues["suppliers_banner_text"] = $values['ex_config']['suppliers_banner_text'];
    //Submitted values for Suppliers fields ends here
    //Submitted values for Product & Tools fields begins here
    $submittedValues["prod_banner_text"] = $values['ex_config']['prod_banner_text'];
    //Submitted values for Product & Tools fields ends here
    //Submitted values for Resources fields begins here
    $submittedValues["res_banner_text"] = $values['ex_config']['res_banner_text'];
    //Submitted values for Resources fields ends here
    //Submitted values for My Account fields begins here
    $submittedValues["my_acc_banner_text"] = $values['ex_config']['my_acc_banner_text'];
    //Submitted values for My Account fields ends here

    //Submitted values for What We Do fields begins here
    if (!empty($values['ex_config']['what_we_do_banner_image'])) {
      $whatWeDoBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['what_we_do_banner_image'][0]);
      $whatWeDoBannerImage->setPermanent();
      $whatWeDoBannerImage->save();

      $this->fileUsage->add($whatWeDoBannerImage, 'What We Do banner', 'what_we_do_banner_image', $values['ex_config']['what_we_do_banner_image'][0]);

      $submittedValues["what_we_do_banner_image"] = $values['ex_config']['what_we_do_banner_image'];
    }

    if (!empty($values['ex_config']['what_we_do_mobile_banner_image'])) {
      $whatWeDoMobileBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['what_we_do_mobile_banner_image'][0]);
      $whatWeDoMobileBannerImage->setPermanent();
      $whatWeDoMobileBannerImage->save();

      $this->fileUsage->add($whatWeDoMobileBannerImage, 'What We Do mobile banner', 'what_we_do_banner_image', $values['ex_config']['what_we_do_mobile_banner_image'][0]);

      $submittedValues["what_we_do_mobile_banner_image"] = $values['ex_config']['what_we_do_mobile_banner_image'];
    }
    //Submitted values for What We Do fields ends here


    //Submitted values for Governments & Partners fields begins here
    if (!empty($values['ex_config']['govt_part_banner_image'])) {
      $govtPartBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['govt_part_banner_image'][0]);
      $govtPartBannerImage->setPermanent();
      $govtPartBannerImage->save();

      $this->fileUsage->add($govtPartBannerImage, 'Governments & Partners banner', 'govt_part_banner_image', $values['ex_config']['govt_part_banner_image'][0]);

      $submittedValues["govt_part_banner_image"] = $values['ex_config']['govt_part_banner_image'];
    }

    if (!empty($values['ex_config']['govt_part_mobile_banner_image'])) {
      $govtPartMobileBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['govt_part_mobile_banner_image'][0]);
      $govtPartMobileBannerImage->setPermanent();
      $govtPartMobileBannerImage->save();

      $this->fileUsage->add($govtPartMobileBannerImage, 'Governments & Partners mobile banner', 'govt_part_banner_image', $values['ex_config']['govt_part_mobile_banner_image'][0]);

      $submittedValues["govt_part_mobile_banner_image"] = $values['ex_config']['govt_part_mobile_banner_image'];
    }
    //Submitted values for Governments & Partners fields ends here


    //Submitted values for Suppliers fields begins here
    if (!empty($values['ex_config']['suppliers_banner_image'])) {
      $suppliersBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['suppliers_banner_image'][0]);
      $suppliersBannerImage->setPermanent();
      $suppliersBannerImage->save();

      $this->fileUsage->add($suppliersBannerImage, 'Suppliers banner', 'suppliers_banner_image', $values['ex_config']['suppliers_banner_image'][0]);

      $submittedValues["suppliers_banner_image"] = $values['ex_config']['suppliers_banner_image'];
    }

    if (!empty($values['ex_config']['suppliers_mobile_banner_image'])) {
      $suppliersMobileBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['suppliers_mobile_banner_image'][0]);
      $suppliersMobileBannerImage->setPermanent();
      $suppliersMobileBannerImage->save();

      $this->fileUsage->add($suppliersMobileBannerImage, 'Suppliers mobile banner', 'suppliers_banner_image', $values['ex_config']['suppliers_mobile_banner_image'][0]);

      $submittedValues["suppliers_mobile_banner_image"] = $values['ex_config']['suppliers_mobile_banner_image'];
    }
    //Submitted values for Suppliers fields ends here

    //Submitted values for Product & Tools fields begins here
    if (!empty($values['ex_config']['prod_banner_image'])) {
      $productsBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['prod_banner_image'][0]);
      $productsBannerImage->setPermanent();
      $productsBannerImage->save();

      $this->fileUsage->add($productsBannerImage, 'Product & Tools banner', 'prod_banner_image', $values['ex_config']['prod_banner_image'][0]);

      $submittedValues["prod_banner_image"] = $values['ex_config']['prod_banner_image'];
    }

    if (!empty($values['ex_config']['prod_mobile_banner_image'])) {
      $productsMobileBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['prod_mobile_banner_image'][0]);
      $productsMobileBannerImage->setPermanent();
      $productsMobileBannerImage->save();

      $this->fileUsage->add($productsMobileBannerImage, 'Product & Tools mobile banner', 'prod_banner_image', $values['ex_config']['prod_mobile_banner_image'][0]);

      $submittedValues["prod_mobile_banner_image"] = $values['ex_config']['prod_mobile_banner_image'];
    }
    //Submitted values for Product & Tools fields ends here

    //Submitted values for Resources fields begins here
    if (!empty($values['ex_config']['res_banner_image'])) {
      $resourcesBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['res_banner_image'][0]);
      $resourcesBannerImage->setPermanent();
      $resourcesBannerImage->save();

      $this->fileUsage->add($resourcesBannerImage, 'Resources banner', 'res_banner_image', $values['ex_config']['res_banner_image'][0]);

      $submittedValues["res_banner_image"] = $values['ex_config']['res_banner_image'];
    }

    if (!empty($values['ex_config']['res_mobile_banner_image'])) {
      $resourcesMobileBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['res_mobile_banner_image'][0]);
      $resourcesMobileBannerImage->setPermanent();
      $resourcesMobileBannerImage->save();

      $this->fileUsage->add($resourcesMobileBannerImage, 'Resources mobile banner', 'res_banner_image', $values['ex_config']['res_mobile_banner_image'][0]);

      $submittedValues["res_mobile_banner_image"] = $values['ex_config']['res_mobile_banner_image'];
    }
    //Submitted values for Resources fields ends here
    
    //Submitted values for My Account fields begins here
    if (!empty($values['ex_config']['my_acc_banner_image'])) {
      $myAccBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['my_acc_banner_image'][0]);
      $myAccBannerImage->setPermanent();
      $myAccBannerImage->save();

      $this->fileUsage->add($myAccBannerImage, 'My Account banner', 'my_acc_banner_image', $values['ex_config']['my_acc_banner_image'][0]);

      $submittedValues["my_acc_banner_image"] = $values['ex_config']['my_acc_banner_image'];
    }

    if (!empty($values['ex_config']['my_acc_mobile_banner_image'])) {
      $myAccMobileBannerImage = $this->entityTypeManager->getStorage('file')->load($values['ex_config']['my_acc_mobile_banner_image'][0]);
      $myAccMobileBannerImage->setPermanent();
      $myAccMobileBannerImage->save();

      $this->fileUsage->add($myAccMobileBannerImage, 'My Account mobile banner', 'my_acc_banner_image', $values['ex_config']['my_acc_mobile_banner_image'][0]);

      $submittedValues["my_acc_mobile_banner_image"] = $values['ex_config']['my_acc_mobile_banner_image'];
    }
    //Submitted values for My Account fields ends here

    $variable = "config_{$userLanguage}";
    // Save the configuration.
    $config = $this->config($this->settings);
    $config->set($variable, $submittedValues);
    $config->save();

    parent::submitForm($form, $form_state);
  }

}