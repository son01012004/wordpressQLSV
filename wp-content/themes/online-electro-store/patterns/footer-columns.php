<?php
 /**
  * Title: Footer Columns
  * Slug: online-electro-store/footer-columns
  */
?>

<!-- wp:group {"style":{"spacing":{"padding":{"right":"0","left":"0","top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"backgroundColor":"footer-bg","layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-group has-footer-bg-background-color has-background" style="padding-top:var(--wp--preset--spacing--30);padding-right:0;padding-bottom:var(--wp--preset--spacing--30);padding-left:0"><!-- wp:spacer {"height":"24px","className":"is-style-has-mb-20"} -->
<div style="height:24px" aria-hidden="true" class="wp-block-spacer is-style-has-mb-20"></div>
<!-- /wp:spacer -->

<!-- wp:columns {"align":"wide","className":"footer-box","style":{"spacing":{"padding":{"top":"0px","bottom":"0px","right":"15px","left":"15px"},"blockGap":{"top":"30px","left":"50px"}}}} -->
<div class="wp-block-columns alignwide footer-box" style="padding-top:0px;padding-right:15px;padding-bottom:0px;padding-left:15px"><!-- wp:column {"width":"30%","className":"footer-contact-box","style":{"spacing":{"blockGap":"24px"}}} -->
<div class="wp-block-column footer-contact-box" style="flex-basis:30%"><!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:site-title {"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"fontSize":"20px","fontStyle":"normal","fontWeight":"400"}},"textColor":"primary","fontFamily":"suez-one"} /-->

<!-- wp:group {"style":{"spacing":{"blockGap":"10px"}},"textColor":"heading","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group has-heading-color has-text-color"><!-- wp:image {"id":100,"sizeSlug":"full","linkDestination":"none","className":"is-style-default is-style-in-flex"} -->
<figure class="wp-block-image size-full is-style-default is-style-in-flex"><img src="<?php echo get_parent_theme_file_uri( '/assets/images/location.png' ); ?>" alt="" class="wp-image-100"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px"}},"textColor":"body-text"} -->
<p class="has-body-text-color has-text-color" style="font-size:14px"><?php esc_html_e('1234 Tech Avenue,  CA 90210, USA','online-electro-store'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"10px","margin":{"top":"var:preset|spacing|20","bottom":"0"}}},"textColor":"heading","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group has-heading-color has-text-color" style="margin-top:var(--wp--preset--spacing--20);margin-bottom:0"><!-- wp:image {"id":8,"sizeSlug":"full","linkDestination":"none","className":"is-style-default is-style-in-flex"} -->
<figure class="wp-block-image size-full is-style-default is-style-in-flex"><img src="<?php echo get_parent_theme_file_uri( '/assets/images/envelope.png' ); ?>" alt="" class="wp-image-8"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px"}},"textColor":"body-text"} -->
<p class="has-body-text-color has-text-color" style="font-size:14px"><?php esc_html_e('support@example.com','online-electro-store'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"10px","margin":{"top":"var:preset|spacing|20","bottom":"0"}}},"textColor":"heading","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group has-heading-color has-text-color" style="margin-top:var(--wp--preset--spacing--20);margin-bottom:0"><!-- wp:image {"id":9,"sizeSlug":"full","linkDestination":"none","className":"is-style-default is-style-in-flex"} -->
<figure class="wp-block-image size-full is-style-default is-style-in-flex"><img src="<?php echo get_parent_theme_file_uri( '/assets/images/phone.png' ); ?>" alt="" class="wp-image-9"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px"}},"textColor":"body-text"} -->
<p class="has-body-text-color has-text-color" style="font-size:14px"><?php esc_html_e('+123-456-7890','online-electro-store'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"spacing":{"blockGap":"24px"}}} -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"16px"}},"textColor":"primary"} -->
<h3 class="wp-block-heading has-primary-color has-text-color" style="font-size:16px;font-style:normal;font-weight:600"><?php esc_html_e('Legal Terms','online-electro-store'); ?></h3>
<!-- /wp:heading -->

<!-- wp:navigation {"customTextColor":"#77808b","overlayMenu":"never","overlayTextColor":"background","className":"is-style-justify-right","style":{"spacing":{"blockGap":"var:preset|spacing|110"},"typography":{"fontSize":"14px"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<!-- wp:navigation-link {"label":"Privacy Policy","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Terms &amp; Conditions","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Cookie Policy","url":"#","kind":"custom","isTopLevelLink":true} /-->
<!-- /wp:navigation --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"spacing":{"blockGap":"24px"}}} -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"16px"}},"textColor":"primary"} -->
<h3 class="wp-block-heading has-primary-color has-text-color" style="font-size:16px;font-style:normal;font-weight:600"><?php esc_html_e('Quick Links','online-electro-store'); ?></h3>
<!-- /wp:heading -->

<!-- wp:navigation {"customTextColor":"#77808b","overlayMenu":"never","overlayTextColor":"background","className":"is-style-justify-right","style":{"spacing":{"blockGap":"var:preset|spacing|110"},"typography":{"fontSize":"14px"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<!-- wp:navigation-link {"label":"Home","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"About Us","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Services","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Products","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Blog","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Contact Us","url":"#","kind":"custom","isTopLevelLink":true} /-->
<!-- /wp:navigation --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"spacing":{"blockGap":"24px"}}} -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"16px"}},"textColor":"primary"} -->
<h3 class="wp-block-heading has-primary-color has-text-color" style="font-size:16px;font-style:normal;font-weight:600"><?php esc_html_e('Customer Support','online-electro-store'); ?></h3>
<!-- /wp:heading -->

<!-- wp:navigation {"customTextColor":"#77808b","overlayMenu":"never","overlayTextColor":"background","className":"is-style-justify-right","style":{"spacing":{"blockGap":"var:preset|spacing|110"},"typography":{"fontSize":"14px"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<!-- wp:navigation-link {"label":"FAQs","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Shipping &amp; Delivery","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Returns &amp; Refunds","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Order Tracking","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Warranty Information","url":"#","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Buy Now","type":"link","opensInNewTab":true,"url":"https://www.cretathemes.com/products/electro-wordpress-theme","kind":"custom","className":"buypro"} /-->
<!-- /wp:navigation --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:spacer {"height":"24px","className":"is-style-has-mb-20"} -->
<div style="height:24px" aria-hidden="true" class="wp-block-spacer is-style-has-mb-20"></div>
<!-- /wp:spacer --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"right":"0","left":"0","top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"gradient":"header-bg","layout":{"type":"constrained","contentSize":"80%"}} -->
<div class="wp-block-group has-header-bg-gradient-background has-background" style="padding-top:var(--wp--preset--spacing--30);padding-right:0;padding-bottom:var(--wp--preset--spacing--30);padding-left:0"><!-- wp:group {"className":"rights-box","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group rights-box"><!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px"},"padding":{"top":"0","right":"0","bottom":"0","left":"0"}},"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"typography":{"fontSize":"14px"}},"textColor":"white"} -->
<p class="has-white-color has-text-color has-link-color" style="margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;font-size:14px"><a rel="noreferrer noopener" href="https://www.cretathemes.com/products/free-electro-store-wordpress-theme"><?php esc_html_e('Online Electro Store','online-electro-store'); ?></a><?php esc_html_e('All Rights Reserved.','online-electro-store'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"typography":{"fontSize":"13px"}},"textColor":"white"} -->
<p class="has-white-color has-text-color has-link-color" style="font-size:13px"><?php esc_html_e('We Using Safe Payments','online-electro-store'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:image {"id":115,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo get_parent_theme_file_uri( '/assets/images/payment.png' ); ?>" alt="" class="wp-image-115"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"primary","textColor":"white","className":"back-to-top","style":{"border":{"radius":"50%"},"elements":{"link":{"color":{"text":"var:preset|color|white"}}}}} -->
<div class="wp-block-button back-to-top"><a class="wp-block-button__link has-white-color has-primary-background-color has-text-color has-background has-link-color wp-element-button" style="border-radius:50%">.</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->