<?php

namespace Drupal\srl_common\Plugin\Block;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id ="download_brocchure_block",
 *   admin_label = @Translation("Download Brocchure Block"),
 *   category = @Translation("Blocks")
 * )
 */
class DownloadBrocchureBlock extends BlockBase {
    /**
     * {@inheritdoc}
     */
    public function build() {
        $webform = \Drupal::entityTypeManager()->getStorage('webform')->load('download_brochure')->getSubmissionForm();
        $downloadBrocchureBlock = '
        <div class="modal fade" id="download_brochure" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Download Brochure</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="download-brochure-form">
                        <div class="response-msg-viewport"></div>
                        <div class="form-viewport">
                        '.render($webform).'
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
        return array(
            '#markup' => t($downloadBrocchureBlock),
            '#cache' => array(
                'max-age' => 0,
            ),
        );
    }
}
