<?php

namespace Drupal\srl_common\Plugin\Block;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id ="Application_confirmation_block",
 *   admin_label = @Translation("Apply application form confirmation Block"),
 *   category = @Translation("Blocks")
 * )
 */
class ApplyApplicationConfirmBlock extends BlockBase {
    /**
     * {@inheritdoc}
     */
    public function build() {
        
        // $resumeApplication = \Drupal::formBuilder()->getForm('\Drupal\srl_common\Form\ResumeApplicationForm');
        $resumeApplicationBlock = '
        <div class="modal fade" id="apply_block_modal_form"  role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content text-dark">
                    <div class="modal-body">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    
                        <p class="form-resume-confirm">
                            Already started the application process? 
                            <button type="button" data-toggle="modal" data-target="#resume_block_modal_form" id="resume-block-open">
                            Continue Enrollment Process
                            </button>
                        </p>
                        <p class="form-application-open">
                            <a href="/application-form?fapp= ">Click Here</a> to start a new application
                        </p>
                    </div>
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
