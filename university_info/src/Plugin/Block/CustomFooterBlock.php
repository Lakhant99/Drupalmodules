<?php

namespace Drupal\university_info\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id ="custom_footer_block",
 *   admin_label = @Translation("Custom Footer Block"),
 *   category = @Translation("Blocks")
 * )
 */
class CustomFooterBlock extends BlockBase {
    /**
     * {@inheritdoc}
     */
    public function build() {
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
        $output = '';
        if($domain == 'srl_stackroute_learning' || $domain == 'stackroute_learning'){
            $output = $this->StackRouteFooter($domain);
        }else{
            $output = $this->UniversityFooter($domain);
        }
        return array(
            '#markup' => t($output),
            '#cache' => array(
                'max-age' => 0,
            ),
        );
    }
    public function StackRouteFooter($domain){
        $output = '
        <div class="container"> 
        <div class="row">
            <div class="col-md-3">
                <div class="latest-tweet">
                <h5>Contact us</h5>
                <p class="text-white">StackRoute Learning Inc.<br><br>
                    <i class="fa fa-map-marker" aria-hidden="true"></i> 2165 Brighton Henrietta Town Line Rd<br>Rochester, NY, 14623<br><br>
                    <a href="mailto:learning@stackroute.com"><i class="fa fa-envelope" aria-hidden="true"></i> learning@stackroute.com</a><br><br>
                </p>
                </div>
            </div>
            <!-- About Us -->
            <div class="col-md-6">
                <div class="small-info"> <img src="https://www.stackroutelearning.com/images/logo-white.png" alt="">
                    <p class="text-white">StackRoute ® provides disruptive IT learning solutions across an ecosystem of partners to produce world class technology professionals that satisfy the needs of tomorrow, today. </p>
                    <ul class="social_icons">
                        <li class="linkedin"><a href="#"><i class="fa fa-linkedin"></i> </a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Patient Guide -->
            <div class="col-md-3">
                <div class="links box-alignment margin-t-80 margin-b-40">
                    <h5>Important Links</h5>
                    <ul>
                        <li><a href="https://www.stackroutelearning.com/universities.html" target="_blank">Universities</a></li>
                        <!--<li><a href="#.">Companies</a></li>-->
                        <!--<li><a href="#.">Students</a></li>-->
                        
                        <li><a href="https://www.stackroutelearning.com/contact.html" target="_blank">Contact Us</a></li>
                        <li><a href="https://www.stackroutelearning.com/privacy.html" target="_blank">Privacy Policy</a></li>
                    </ul>
                </div>
				<div class="links box-alignment margin-t-20 margin-b-40">
                    <h5>Our Programs</h5>
                    <ul>
                        <li><a href="https://www.stackroutelearning.com/programs-software-engineering.html" target="_blank">Software Engineering</a></li>
                        <li><a href="https://www.stackroutelearning.com/programs-data-science.html" target="_blank">Data Science</a></li>
                        <li><a href="https://www.stackroutelearning.com/programs-cyber-security.html" target="_blank">Cyber Security</a></li>
                        <li><a href="https://www.stackroutelearning.com/programs-cloud-devops.html" target="_blank">Cloud Computing</a></li>
                    </ul>
                </div>
            </div>
				
            </div>
           
        </div>
        <div class="rights"><p>© 2021 StackRoute Learning - an NIIT Venture.</p></div>';

        return $output;
    }
    public function UniversityFooter($domain){
        $output = '';
        $domain = $domain;
        $config = \Drupal::config('university_info.config');
        $footer_logo = '';
        $site_name_suffix = '';
        $site_name_prefix = '';
        if($config->get($domain.'_logo_footer') && $config->get($domain.'_logo_footer') > 0){
            $file = \Drupal\file\Entity\File::load($config->get($domain.'_logo_footer'));
            if($file){
               $footer_logo = file_url_transform_relative(file_create_url($file->getFileUri()));
               $footer_logo = '<img class="lazyload" alt="footer logo" width="100%" height="100%" data-src="'.$footer_logo.'" />';
            }
        }
        $social_links = '';
        if($config->get($domain.'_facebook')){
            $social_links.= '<li><a href="'.$config->get($domain.'_facebook').'" target="_blank"><img alt="" class=" lazyload" data-src="/themes/hilbert/images/facebook.png" /></a>';
        }
        if($config->get($domain.'_linkedin')){
            $social_links.= '<li><a href="'.$config->get($domain.'_linkedin').'" target="_blank"><img alt="" src="/themes/hilbert/images/linkedin.png" /></a>';
        }
        if($domain == 'srl_hilbert_qc_niit_mts_com' ||  $domain == 'srl_qc2_niit_mts_com'){
            $footer_logo.='<h6>Powered by StackRoute Learning, Inc</h6>';
        }
        $copyright = '<p class="copyright">Powered by StackRoute Learning, Inc</p>';
        if($domain == 'unh_bootcamps_education' ||  $domain == 'srl_unh_qc_niit_mts_com'){
            $copyright = '<p class="copyright"><a href="https://training.unh.edu" target="_blank">Powered by StackRoute Learning, Inc and UNH Professional Development &amp; Training</a></p>';
        }elseif( $domain == 'srl_qc_niit_mts_com'){
            $copyright = ' ';
            $site_name_suffix = 'OPCE';
        }elseif( $domain == 'stackroute_suffolk_university'){
        $copyright = ' ';
        $site_name_suffix = 'Center for Continuing and Professional Education';
        }elseif ( $domain == 'srl_pointpark_qc_niit_mts_com' || $domain == 'point_park_university'){
        $copyright = ' ';
        }elseif( $domain == 'mercy_edu' || $domain == 'stackroute_mercy_college'){
            $site_name_prefix = 'CERTIFi by ';
        }
        $cookies_setting = '<a id="hs_show_banner_button" onClick="(function(){
        var _hsp = window._hsp = window._hsp || [];
        _hsp.push([\'showBanner\']);})()">Cookie Settings</a>';
        $output.='
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-5 my-4 ml-5">
                    <div class="login-footer">
                        '.$footer_logo.'
                        <ul class="list-inline socialmedia-footer">
                            '.$social_links.'
                        </ul>
                        <div class="copyright">
                            <p>© 2023 '.$site_name_prefix.''.\Drupal::config('system.site')->get('name').' '.$site_name_suffix.'</p>
                            '.$copyright.'
                        </div>
                    </div>
                </div>
                <div class="col-md-5 offset-md-1 needinfo-footer">
                    <div class="alpha-info-container">
                        <h2>Need More Information?</h2>
                        <p>Learn more about the bootcamps by reaching out to support services.</p>
                        <p class="call-us">Call : <span>'.$config->get($domain.'_phone').'</span></p>
                        <div class="row">
                            <div class="col-sm-5">';
                                if($domain == 'srl_qc_niit_mts_com'){
                                    $output.=' <a class="alpha-uppercase alpha-bordered-btn" href="/support">Contact Us</a>';
                                }else{
                                    $output.=' <a class="alpha-uppercase alpha-bordered-btn" href="/contact-us">Contact Us</a>';
                                }
                            $output.='</div>
                            <div class="col-sm-7 text-right footer-link">
                                <a href="/privacy-policy">Privacy Policy</a> <span class="seperator">| </span><a href="/term-of-use">Terms of Use</a>
                                <span class="seperator">| </span>'.$cookies_setting.'
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ';
        return $output;
    }
}