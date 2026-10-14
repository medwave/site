<?php
/**
 * Title: Service page
 * Slug: medwave/page-service
 * Categories: medwave
 * Post Types: page
 * Block Types: core/post-content
 * Description: Layout for a single service page: intro, what's included, FAQ and call to action.
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|50"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--50)">
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Service name</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"muted","fontSize":"large"} -->
<p class="has-muted-color has-text-color has-large-font-size">One or two sentences on the problem this service solves for a practice.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/contact/">Talk to us</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--30)">
<!-- wp:heading -->
<h2 class="wp-block-heading">What's included</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
<!-- wp:list-item --><li>First thing included</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>Second thing included</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>Third thing included</li><!-- /wp:list-item -->
</ul>
<!-- /wp:list -->
</div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"medwave/faq"} /-->
<!-- wp:pattern {"slug":"medwave/cta"} /-->
