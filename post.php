<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<article class="container single-post">
    <header class="single-header">
        <p class="eyebrow"><?php $this->category(', '); ?></p>
        <h1><?php $this->title(); ?></h1>
        <div class="post-meta"><time datetime="<?php $this->date('c'); ?>"><?php $this->date('Y 年 m 月 d 日'); ?></time><?php if (lumino_theme_option('showReadingTime', '1') === '1'): ?><span>·</span><span><?php echo lumino_reading_time($this->content); ?> 分钟阅读</span><?php endif; ?></div>
    </header>
    <div class="single-layout">
        <div class="post-content" data-reading-content><?php $this->content(); ?></div>
        <?php if (lumino_theme_option('showToc', '1') === '1'): ?><aside class="toc"><p class="eyebrow">本文目录</p><div data-toc>本文目录</div></aside><?php endif; ?>
    </div>
    <footer class="single-footer">
        <div class="tag-list"><?php $this->tags(' ', true, ''); ?></div>
        <div class="post-tools"><button class="copy-link" type="button" data-copy-link title="复制文章链接">复制链接</button><div class="author-sign"><span class="author-avatar small">L</span><span>作者 <?php echo lumino_escape(lumino_theme_option('authorName', 'Lumino Editor')); ?></span></div></div>
    </footer>
    <?php $this->need('comments.php'); ?>
</article>
<?php $this->need('footer.php'); ?>
