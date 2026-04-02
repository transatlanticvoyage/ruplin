<?php
/**
 * Polyansk Service Categories Tiles — front-end template
 *
 * Variables available:
 *   $categories — array of category objects, each with:
 *       ->category_id
 *       ->category_name
 *       ->category_description
 *       ->service_pages  (array of objects with ->moniker, ->post_id, ->post_name)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<section class="polyansk-tiles-section">
    <div class="polyansk-tiles-grid">
        <?php foreach ($categories as $cat) : ?>
            <div class="polyansk-tile">
                <h3 class="polyansk-tile-title"><?php echo esc_html($cat->category_name); ?></h3>

                <?php if (!empty($cat->category_description)) : ?>
                    <p class="polyansk-tile-description"><?php echo esc_html($cat->category_description); ?></p>
                <?php endif; ?>

                <?php if (!empty($cat->service_pages)) : ?>
                    <ul class="polyansk-tile-links">
                        <?php foreach ($cat->service_pages as $page) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_permalink($page->post_id)); ?>">
                                    <?php echo esc_html($page->moniker); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
