<?php

namespace Drupal\university_info\Plugin\Block;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id ="custom_header_logo_block",
 *   admin_label = @Translation("Custom header logo Block"),
 *   category = @Translation("Blocks")
 * )
 */
class CustomLogoBlock extends BlockBase {
    /**
     * {@inheritdoc}
     */
    public function build() {
        $output = '';
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
        $config = \Drupal::config('university_info.config');
        $footer_logo = '';
        if($config->get($domain.'_logo_header') && $config->get($domain.'_logo_header') > 0){
            $file = \Drupal\file\Entity\File::load($config->get($domain.'_logo_header'));
            if($file){
               $logo = file_url_transform_relative(file_create_url($file->getFileUri()));
               $suffolk = '';
               if($domain == 'stackroute_suffolk_university'){
                $suffolk = 'logo-transform';
               }
               $output.='<div class="'.$suffolk.'"><div class="faq_tabing"><a class="navbar-brand" href="/"><img alt="logo" src="'.$logo.'" />';
               if($domain == 'srl_hilbert_qc_niit_mts_com' ||  $domain == 'srl_qc2_niit_mts_com'){
                   $output.='<span>Powered by StackRoute Learning, Inc</span>';
               }
               $output.='</a></div></div>';
            }
        }
        return array(
            '#markup' => t($output),
            '#cache' => array(
                'max-age' => 0,
            ),
        );
    }
}
