<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $luminoCheckinEnabled = !empty(lumino_options()->allowRegister) && lumino_theme_option('showCheckin', '1') === '1'; ?>
<aside class="sidebar">
    <section class="sidebar-block author-block">
        <p class="eyebrow">关于作者</p>
        <div class="author-avatar"><?php if (lumino_theme_option('authorAvatar')): ?><img src="<?php echo lumino_escape(lumino_theme_option('authorAvatar')); ?>" alt="<?php echo lumino_escape(lumino_theme_option('authorName', 'Lumino Editor')); ?>"><?php else: ?>L<?php endif; ?></div>
        <h3><?php echo lumino_escape(lumino_theme_option('authorName', 'Lumino Editor')); ?></h3>
        <p><?php echo nl2br(lumino_escape(lumino_theme_option('authorBio', '写下值得被记住的片段。'))); ?></p>
    </section>
    <?php if ($luminoCheckinEnabled): ?><section class="sidebar-block checkin-block"><p class="eyebrow">每日签到</p><p class="checkin-note"><?php echo lumino_escape(lumino_theme_option('checkinNote', '保存今天的一点心情')); ?></p><button class="checkin-card-button" type="button" data-checkin data-checkin-label="<?php echo lumino_escape(lumino_theme_option('checkinLabel', '今日签到')); ?>"><span class="checkin-icon" data-checkin-icon aria-hidden="true">+</span><span class="checkin-card-copy"><strong data-checkin-label-text><?php echo lumino_escape(lumino_theme_option('checkinLabel', '今日签到')); ?></strong><small data-checkin-streak></small></span><span class="checkin-arrow" aria-hidden="true">→</span></button></section><?php endif; ?>
    <section class="sidebar-block">
        <p class="eyebrow">探索</p>
        <div class="sidebar-links"><?php foreach (lumino_explore_items() as $item): ?><a href="<?php echo lumino_escape($item['url']); ?>"><?php echo lumino_escape($item['label']); ?><span>↗</span></a><?php endforeach; ?></div>
    </section>
</aside>
