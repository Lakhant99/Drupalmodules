<?php

namespace Drupal\srl_common\Plugin\Block;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id ="resume_application_block",
 *   admin_label = @Translation("Resume Application Block"),
 *   category = @Translation("Blocks")
 * )
 */
class ResumeApplicationBlock extends BlockBase {
    /**
     * {@inheritdoc}
     */
    public function build() {
        $resumeApplication = \Drupal::entityTypeManager()->getStorage('webform')->load('resume_application')->getSubmissionForm();
        // $resumeApplication = \Drupal::formBuilder()->getForm('\Drupal\srl_common\Form\ResumeApplicationForm');
        $resumeApplicationBlock = '
        <div class="modal fade" id="resume_block_modal_form"  role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="close btnpopupclose" data-dismiss="modal">&times;</button>';
        $resumeApplicationBlock.='<div id="resumeapplication"><div class="form-resumeapplication">'.render($resumeApplication).'</div></div>';
        // $resumeApplicationBlock.='<div id="build-resumeapplication"></div>';
                    $resumeApplicationBlock.='</div>
                </div>
            </div>
        </div>
        ';
        return array(
            '#markup' => t($resumeApplicationBlock),
            '#cache' => array(
                'max-age' => 0,
            ),
        );
    }
}

