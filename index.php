<?php
/**
 * Lumino 轻语博客
 *
 * @package Lumino 轻语博客
 * @author Lumino Team
 * @version 26.0
 * @link https://lumino.xmya.top
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$cover = lumino_theme_option('heroImage', lumino_theme_asset('assets/forest-station.jpg'));
?>
<?php if (lumino_theme_option('heroEnabled', '1') === '1'): ?>
<section class="cover-hero" style="--cover-image:url('<?php echo lumino_escape($cover); ?>')">
    <div class="cover-veil"></div>
    <div class="container cover-content">
        <p class="cover-kicker"><?php echo lumino_escape(lumino_theme_option('heroKicker', 'LUMINO · 轻语博客')); ?></p>
        <h1><?php echo lumino_escape(lumino_theme_option('heroTitle', '把思绪放轻，把表达放远。')); ?></h1>
        <p><?php echo nl2br(lumino_escape(lumino_theme_option('heroSubtitle', lumino_theme_option('siteDescription', '一个留白充足的写作空间，让每一次记录都清晰、安静而有光。')))); ?></p>
        <a class="cover-action" href="#latest"><?php echo lumino_escape(lumino_theme_option('heroActionText', '开始阅读')); ?> <span>↓</span></a>
    </div>
</section>
<?php endif; ?>

<section class="container content-layout" id="latest">
    <div class="posts-column">
        <div class="section-intro"><p><?php echo lumino_escape(lumino_theme_option('recentKicker', '最近更新')); ?></p><h2><?php echo lumino_escape(lumino_theme_option('recentTitle', '近期文章')); ?></h2></div>
        <?php $postIndex = 0; while ($this->next()): $postIndex++; ?>
        <article class="post-card">
            <span class="post-card-index"><?php echo str_pad((string) $postIndex, 2, '0', STR_PAD_LEFT); ?></span>
            <div>
                <div class="post-meta"><time datetime="<?php $this->date('c'); ?>"><?php $this->date('Y.m.d'); ?></time><?php if ($this->categories): ?><b>·</b><span><?php $this->category(', '); ?></span><?php endif; ?></div>
                <h3><a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h3>
                <p><?php $this->excerpt((int) lumino_theme_option('excerptLength', '180'), '…'); ?></p>
                <a class="read-link" href="<?php $this->permalink(); ?>"><?php echo lumino_escape(lumino_theme_option('readMoreText', '阅读全文')); ?> <span>→</span></a>
            </div>
        </article>
        <?php endwhile; ?>
        <div class="pagination"><?php $this->pageLink('更早的文章 →', 'next'); ?><?php $this->pageLink('← 更新的文章', 'prev'); ?></div>
    </div>
    <?php if (lumino_theme_option('showSidebar', '1') === '1') $this->need('sidebar.php'); ?>
</section>

<?php if (lumino_theme_option('showCategories', '1') === '1'): ?>
<section class="container category-section">
    <div class="section-intro"><p><?php echo lumino_escape(lumino_theme_option('categoryKicker', '分类')); ?></p><h2><?php echo lumino_escape(lumino_theme_option('categoryTitle', '按主题浏览')); ?></h2><span><?php echo lumino_escape(lumino_theme_option('categoryIntro', '从不同角度，回看写下的片段。')); ?></span></div>
    <div class="category-grid category-<?php echo lumino_escape(lumino_theme_option('categoryLayout', 'grid')); ?>">
        <?php $categorySettings = lumino_category_settings(); $categoryWidget = \Widget\Metas\Category\Rows::alloc(); $categoryWidget->to($categories); $categoryCount = 0; while ($categories->next()): $slug = (string) $categories->slug; $setting = isset($categorySettings[$slug]) ? $categorySettings[$slug] : array('label' => $categories->name, 'description' => '', 'color' => '#9ccfbc'); if (lumino_theme_option('categoryLimit', '6') !== '0' && $categoryCount >= (int) lumino_theme_option('categoryLimit', '6')) break; $categoryCount++; ?>
        <a class="category-card" href="<?php $categories->permalink(); ?>" style="--category-accent:<?php echo lumino_escape($setting['color']); ?>"><span><?php echo str_pad((string) $categoryCount, 2, '0', STR_PAD_LEFT); ?> / <?php echo (int) $categories->count; ?> 篇</span><strong><?php echo lumino_escape($setting['label']); ?></strong><small><?php echo lumino_escape($setting['description']); ?></small><i>↗</i></a>
        <?php endwhile; ?>
    </div>
</section>
<?php endif; ?>
<?php $this->need('footer.php'); ?>
