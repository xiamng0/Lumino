<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$luminoHasCover = $this->is('index') && lumino_theme_option('heroEnabled', '1') === '1';
$luminoOverlay = lumino_theme_option('heroOverlay', 'balanced');
?>
<!doctype html>
<html lang="<?php $this->options->lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="<?php echo lumino_color(lumino_theme_option('paperColor', '#fbfdfc'), '#fbfdfc'); ?>">
    <meta name="description" content="<?php echo lumino_escape(lumino_theme_option('siteDescription', '一个留白充足的写作空间，让每一次记录都清晰、安静而有光。')); ?>">
    <title><?php $this->archiveTitle('%s - '); ?><?php echo lumino_escape(lumino_theme_option('siteName', 'Lumino 轻语博客')); ?></title>
    <link rel="stylesheet" href="<?php $this->options->themeUrl('style.css'); ?>">
    <link rel="icon" href="<?php echo lumino_escape(lumino_theme_option('faviconUrl', lumino_theme_asset('logo.svg'))); ?>">
    <style>:root{--accent:<?php echo lumino_color(lumino_theme_option('accentColor', '#9ccfbc'), '#9ccfbc'); ?>;--paper:<?php echo lumino_color(lumino_theme_option('paperColor', '#fbfdfc'), '#fbfdfc'); ?>;--paper-white:<?php echo lumino_color(lumino_theme_option('surfaceColor', '#f1faf6'), '#f1faf6'); ?>}</style>
    <?php if (lumino_theme_option('customCss')): ?><style><?php echo lumino_theme_option('customCss'); ?></style><?php endif; ?>
    <?php $this->header('generator=&template=&pingback=&xmlrpc=&wlw=&rss1=&rss2=&atom=&commentReply='); ?>
    <?php if (lumino_theme_option('customHead')) echo lumino_theme_option('customHead'); ?>
</head>
<body class="lumino-theme blur-<?php echo lumino_escape(lumino_theme_option('blurIntensity', 'medium')); ?> overlay-<?php echo lumino_escape($luminoOverlay); ?><?php echo $luminoHasCover ? ' has-cover' : ' has-paper-header'; ?>">
<div class="site-noise" aria-hidden="true"></div>
<?php if (lumino_theme_option('showReadingProgress', '1') === '1'): ?><div class="reading-progress" data-reading-progress aria-hidden="true"><span></span></div><?php endif; ?>
<header class="site-header<?php echo $luminoHasCover ? ' on-cover' : ' on-paper'; ?>"><div class="container header-inner">
    <a class="brand" href="<?php $this->options->siteUrl(); ?>" aria-label="<?php echo lumino_escape(lumino_theme_option('siteName', 'Lumino 轻语博客')); ?>">
        <?php $logo = lumino_theme_option('logoUrl'); ?><img class="brand-logo" src="<?php echo lumino_escape($logo ?: lumino_theme_asset('logo.svg')); ?>" alt="">
        <span class="brand-copy"><strong><?php echo lumino_escape(lumino_theme_option('siteName', 'Lumino 轻语博客')); ?></strong><small><?php echo lumino_escape(lumino_theme_option('siteTagline', '记录灵感，也记录生活')); ?></small></span>
    </a>
    <button class="menu-toggle" type="button" aria-label="打开菜单" aria-expanded="false"><span></span><span></span></button>
    <nav class="site-nav" aria-label="主导航">
        <?php foreach (lumino_nav_items() as $item): ?><a href="<?php echo lumino_escape($item['url']); ?>"><?php echo lumino_escape($item['label']); ?></a><?php endforeach; ?>
        <button class="search-toggle" type="button" aria-label="打开搜索" aria-expanded="false" data-search-toggle><span>⌕</span><i>搜索</i></button>
    </nav>
</div></header>
<div class="search-panel" data-search-panel><div class="container"><form method="get" action="<?php $this->options->siteUrl(); ?>"><label for="lumino-search">搜索文章</label><div class="search-row"><input id="lumino-search" name="s" type="search" placeholder="输入关键词" autocomplete="off"><button type="submit">开始搜索</button></div></form></div></div>
<main class="site-main">
