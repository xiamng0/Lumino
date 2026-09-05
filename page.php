<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<article class="container single-post page-post"><header class="single-header"><p class="eyebrow">PAGE</p><h1><?php $this->title(); ?></h1></header><div class="post-content"><?php $this->content(); ?></div><?php $this->need('comments.php'); ?></article>
<?php $this->need('footer.php'); ?>
