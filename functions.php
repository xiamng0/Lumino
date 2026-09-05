<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

function lumino_options()
{
    static $options = null;
    if ($options === null) {
        $options = \Widget\Options::alloc();
    }
    return $options;
}

function lumino_theme_option($key, $default = '')
{
    $options = lumino_options();
    $value = isset($options->$key) ? $options->$key : '';
    if ($value === '' && $key === 'siteName') {
        $value = isset($options->brandName) ? $options->brandName : '';
    }
    if ($value === '' && $key === 'siteTagline') {
        $value = isset($options->tagline) ? $options->tagline : '';
    }
    return $value === '' ? $default : $value;
}

function lumino_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function lumino_color($value, $default)
{
    $value = trim((string) $value);
    return preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $value) ? $value : $default;
}

function lumino_theme_asset($path)
{
    $options = lumino_options();
    return $options->themeUrl(null, $options->theme) . '/' . ltrim($path, '/');
}

function lumino_page_items()
{
    static $items = null;
    if ($items !== null) {
        return $items;
    }

    $items = array();
    \Widget\Contents\Page\Rows::alloc()->to($pages);
    while ($pages->next()) {
        $slug = trim((string) $pages->slug);
        if ($slug !== '') {
            $items[$slug] = array(
                'label' => (string) $pages->title,
                'url' => (string) $pages->permalink
            );
        }
    }

    return $items;
}

function lumino_page_url($slug)
{
    $pages = lumino_page_items();
    return isset($pages[$slug]) ? $pages[$slug]['url'] : '';
}

function lumino_resolve_nav_target($target)
{
    $target = trim((string) $target);
    $options = lumino_options();

    if ($target === 'home' || $target === '/') {
        return (string) $options->siteUrl;
    }

    if (strpos($target, 'page:') === 0) {
        $slug = trim(substr($target, 5));
        if ($slug === 'archives') {
            $slug = lumino_theme_option('archivePageSlug', 'archives');
        } elseif ($slug === 'about') {
            $slug = lumino_theme_option('aboutPageSlug', 'about');
        }
        return lumino_page_url($slug);
    }

    // Migrate the old default links without retaining their hard-coded routes.
    if ($target === 'archives.html' || $target === '/archives.html') {
        return lumino_page_url(lumino_theme_option('archivePageSlug', 'archives'));
    }

    if ($target === 'about.html' || $target === '/about.html') {
        return lumino_page_url(lumino_theme_option('aboutPageSlug', 'about'));
    }

    return $target;
}

function lumino_nav_items()
{
    $raw = lumino_theme_option('navLinks', "首页|home\n归档|page:archives\n关于|page:about");
    $items = array();
    $linkedPages = array();
    foreach (preg_split('/\r?\n/', $raw) as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
            $url = lumino_resolve_nav_target($parts[1]);
            if ($url !== '') {
                $items[] = array('label' => $parts[0], 'url' => $url);
                if (strpos($parts[1], 'page:') === 0) {
                    $linkedPages[] = trim(substr($parts[1], 5));
                }
            }
        }
    }

    if (lumino_theme_option('appendPages', '1') === '1') {
        foreach (lumino_page_items() as $slug => $page) {
            if (!in_array($slug, $linkedPages, true)) {
                $items[] = array('label' => $page['label'], 'url' => $page['url']);
            }
        }
    }

    return $items;
}

function lumino_explore_items()
{
    $items = array();
    $archiveUrl = lumino_page_url(lumino_theme_option('archivePageSlug', 'archives'));
    $aboutUrl = lumino_page_url(lumino_theme_option('aboutPageSlug', 'about'));

    if ($archiveUrl !== '') {
        $items[] = array('label' => '文章归档', 'url' => $archiveUrl);
    }
    if ($aboutUrl !== '') {
        $items[] = array('label' => '关于这个空间', 'url' => $aboutUrl);
    }
    $items[] = array('label' => '订阅 RSS', 'url' => (string) lumino_options()->feedUrl);

    return $items;
}

function lumino_social_items()
{
    $raw = lumino_theme_option('socialLinks', "RSS|/feed\nGitHub|https://github.com/");
    $items = array();
    foreach (preg_split('/\r?\n/', $raw) as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
            $items[] = array('label' => $parts[0], 'url' => $parts[1]);
        }
    }
    return $items;
}

function lumino_category_settings()
{
    $raw = lumino_theme_option('categorySettings', "default|随笔|记录思考与日常|#9ccfbc\ntech|技术|工具、代码与产品观察|#a7d7cb\ndesign|设计|审美、界面与灵感|#bfdacb");
    $items = array();
    foreach (preg_split('/\r?\n/', $raw) as $line) {
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) >= 2 && $parts[0] !== '') {
            $items[$parts[0]] = array(
                'label' => $parts[1] !== '' ? $parts[1] : $parts[0],
                'description' => isset($parts[2]) ? $parts[2] : '',
                'color' => isset($parts[3]) ? lumino_color($parts[3], '#9ccfbc') : '#9ccfbc'
            );
        }
    }
    return $items;
}

function lumino_reading_time($content)
{
    if (is_object($content)) {
        $content = method_exists($content, '__toString') ? (string) $content : '';
    }
    $text = trim(strip_tags($content));
    $words = preg_match_all('/[\x{4e00}-\x{9fff}]|[A-Za-z0-9]+/u', $text, $matches);
    return max(1, (int) ceil($words / 350));
}

function lumino_admin_control_center($form)
{
    $uploadUrl = '';
    try {
        $uploadUrl = \Widget\Security::alloc()->getIndex('/action/upload');
    } catch (\Throwable $error) {
        // The visual uploader is available only in a logged-in Typecho backend request.
    }

    $panel = new \Typecho\Widget\Helper\Layout('div');
    $panel->setAttribute('id', 'lumino-admin-assets');
    $panel->html(str_replace('__LUMINO_UPLOAD_URL__', json_encode($uploadUrl), <<<'HTML'
<style>
#lumino-control-center{width:100%;margin:0 0 24px;border:1px solid #dce9e2;border-radius:10px;overflow:hidden;background:#f8fcfa;box-shadow:0 18px 42px rgba(39,77,61,.08)}
body.lumino-admin-wide .main>.body.container,body:has(#lumino-control-center) .main>.body.container{width:100%;max-width:none;padding-left:clamp(16px,3vw,48px);padding-right:clamp(16px,3vw,48px)}
body.lumino-admin-wide .typecho-page-main,body:has(#lumino-control-center) .typecho-page-main{width:100%;margin-left:0;margin-right:0}
body.lumino-admin-wide .typecho-page-main>[role=form],body:has(#lumino-control-center) .typecho-page-main>[role=form]{flex:0 0 100%;max-width:100%;margin-left:0}
body.lumino-admin-wide .typecho-page-title,body.lumino-admin-wide .typecho-option-tabs,body:has(#lumino-control-center) .typecho-page-title,body:has(#lumino-control-center) .typecho-option-tabs{width:100%}
#lumino-control-center *{box-sizing:border-box}
.lumino-control-header{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;padding:31px 34px;color:#eff8f3;background:#27473b}
.lumino-control-kicker{margin:0 0 9px;color:#b9dac9;font-size:11px;letter-spacing:.12em}
.lumino-control-header h2{margin:0;font-size:28px;line-height:1.2;color:#fff}
.lumino-control-header p{max-width:360px;margin:0;color:#c9ddd1;font-size:13px;line-height:1.7}
.lumino-control-body{display:grid;grid-template-columns:184px minmax(0,1fr);min-height:560px}
.lumino-control-menu{padding:18px 12px;border-right:1px solid #deebe4;background:#eff7f3}
.lumino-control-menu button{display:flex;align-items:center;width:100%;margin:2px 0;padding:10px 12px;border:0;border-radius:6px;color:#527063;background:transparent;text-align:left;font-size:13px;cursor:pointer;transition:background .18s,color .18s}
.lumino-control-menu button:hover{color:#27473b;background:#e1f1e8}
.lumino-control-menu button.is-current{color:#fff;background:#487d68;box-shadow:0 7px 15px rgba(57,107,85,.18)}
.lumino-control-summary{margin:20px 8px 0;padding:15px 12px;border-top:1px solid #d7e7de;color:#6e887c;font-size:11px;line-height:1.65}
.lumino-control-summary strong{display:block;margin-bottom:3px;color:#385c4d;font-size:12px}
.lumino-control-panels{padding:30px 34px;background:#fff}
.lumino-control-panel{display:none}
.lumino-control-panel.is-current{display:block}
.lumino-control-panel-header{margin:0 0 22px;padding-bottom:17px;border-bottom:1px solid #e7f0eb}
.lumino-control-panel-header h3{margin:0 0 5px;color:#29473b;font-size:19px}
.lumino-control-panel-header p{margin:0;color:#7a9186;font-size:12px;line-height:1.6}
.lumino-about-card{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.lumino-about-item{padding:18px;border:1px solid #dbeae1;border-radius:8px;background:#f9fdfb}
.lumino-about-item.is-wide{grid-column:1/-1}
.lumino-about-item small{display:block;margin-bottom:7px;color:#7d9589;font-size:11px;letter-spacing:.04em}
.lumino-about-item strong{display:block;color:#2f5545;font-size:17px;line-height:1.45}
.lumino-about-item p{margin:8px 0 0;color:#6e887c;font-size:12px;line-height:1.75}
.lumino-about-item a{color:#467d66;word-break:break-all}
.lumino-about-item a:hover{color:#2d5c49;text-decoration:underline;text-underline-offset:3px}
.lumino-control-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 18px}
.lumino-control-fields .typecho-option{min-width:0;margin:0;padding:0;border:0;list-style:none}
.lumino-control-fields .lumino-managed-source{display:none}
.lumino-control-fields .typecho-option:has(textarea){grid-column:1/-1}
.lumino-control-fields .typecho-label{display:block;margin:0 0 7px;color:#35564a;font-size:13px;font-weight:600}
.lumino-control-fields .description{margin:7px 0 0;color:#85998f;font-size:11px;line-height:1.55}
.lumino-control-fields input.text,.lumino-control-fields textarea,.lumino-control-fields select{width:100%;border:1px solid #dbe9e1;border-radius:6px;color:#29473b;background:#fbfefc;box-shadow:none;transition:border-color .18s,box-shadow .18s}
.lumino-control-fields input.text,.lumino-control-fields select{height:38px;padding:0 10px}
.lumino-control-fields textarea{min-height:102px;padding:10px;line-height:1.65;resize:vertical}
.lumino-control-fields input.text:focus,.lumino-control-fields textarea:focus,.lumino-control-fields select:focus{border-color:#7ab99e;outline:0;box-shadow:0 0 0 3px rgba(139,202,174,.17)}
.lumino-control-fields .multiline{display:inline-flex;align-items:center;gap:7px;margin:3px 14px 3px 0;color:#567569;font-size:12px}
.lumino-control-fields .multiline input{accent-color:#5b997f}
.lumino-structured-editor{grid-column:1/-1;padding:17px;border:1px solid #dbeae1;border-radius:8px;background:#f9fdfb}
.lumino-structured-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:13px}
.lumino-structured-heading strong{display:block;color:#36584a;font-size:13px}
.lumino-structured-heading small{display:block;margin-top:3px;color:#81968b;font-size:11px;line-height:1.5}
.lumino-structured-list{display:grid;gap:8px}
.lumino-structured-row{display:grid;grid-template-columns:repeat(var(--lumino-columns),minmax(0,1fr)) 30px;gap:8px;align-items:center}
.lumino-structured-row input{width:100%;min-width:0;height:35px;padding:0 9px;border:1px solid #d8e8df;border-radius:5px;color:#305144;background:#fff;font-size:12px}
.lumino-structured-row input[type=color]{padding:3px}
.lumino-icon-button{display:grid;place-items:center;width:30px;height:30px;border:1px solid #d7e7de;border-radius:5px;color:#6f887d;background:#fff;font-size:17px;line-height:1;cursor:pointer}
.lumino-icon-button:hover{border-color:#d69a9a;color:#a65555;background:#fff7f7}
.lumino-add-row{margin-top:11px;border:1px dashed #9ec8b4;border-radius:5px;padding:8px 10px;color:#46765f;background:#f2faf5;font-size:12px;cursor:pointer}
.lumino-add-row:hover{background:#e6f5ed}
.lumino-image-editor{display:grid;grid-template-columns:54px minmax(0,1fr);gap:11px;align-items:center;padding:10px 11px;border:1px solid #dbeae1;border-radius:8px;background:#f9fdfb}
.lumino-image-preview{display:grid;place-items:center;overflow:hidden;width:54px;height:54px;border-radius:7px;color:#82a293;background:#e9f5ee;font-size:11px}
.lumino-image-preview img{width:100%;height:100%;object-fit:cover}
.lumino-image-editor strong{display:block;color:#36584a;font-size:12px}
.lumino-image-actions{display:flex;align-items:center;gap:8px;margin-top:7px}
.lumino-upload-button{display:inline-flex;align-items:center;border:1px solid #c9dfd3;border-radius:5px;padding:6px 8px;color:#3e705a;background:#fff;font-size:11px;cursor:pointer}
.lumino-upload-button:hover{background:#eaf7ef}
.lumino-upload-status{color:#82978c;font-size:11px}
.lumino-color-editor{display:grid;grid-template-columns:48px minmax(0,180px) 1fr;align-items:center;gap:9px;margin-top:4px}
.lumino-color-editor input[type=color]{width:48px;height:38px;padding:3px;border:1px solid #dbe9e1;border-radius:6px;background:#fff;cursor:pointer}
.lumino-color-editor input[type=text]{width:100%;height:38px;padding:0 10px;border:1px solid #dbe9e1;border-radius:6px;color:#29473b;background:#fbfefc;font:12px monospace}
.lumino-color-editor input[type=text]:focus{border-color:#7ab99e;outline:0;box-shadow:0 0 0 3px rgba(139,202,174,.17)}
.lumino-color-editor output{height:28px;border-radius:5px;border:1px solid rgba(58,103,87,.12);background:#9ccfbc}
.lumino-color-help{color:#82978c;font-size:11px}
.lumino-control-save{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:0;padding:15px 34px;border-top:1px solid #e3eee8;background:rgba(247,252,249,.93)}
.lumino-control-save small{color:#7a9186;font-size:12px}
.lumino-control-save .typecho-option-submit{margin:0;padding:0;border:0;background:transparent}
.lumino-control-save button[type=submit]{border:0;border-radius:6px;padding:10px 18px;color:#fff;background:#42735f;font-size:13px;cursor:pointer;box-shadow:0 7px 15px rgba(61,109,89,.2)}
.lumino-control-save button[type=submit]:hover{background:#315c4b}
.lumino-settings> .typecho-option{display:none}
@media(max-width:800px){.lumino-control-header{align-items:flex-start;flex-direction:column;padding:25px 22px}.lumino-control-body{grid-template-columns:1fr}.lumino-control-menu{display:flex;gap:4px;overflow:auto;padding:10px;border-right:0;border-bottom:1px solid #dce9e2}.lumino-control-menu button{width:auto;white-space:nowrap}.lumino-control-summary{display:none}.lumino-control-panels{padding:22px 18px}.lumino-control-fields{grid-template-columns:1fr}.lumino-control-save{padding:14px 18px}.lumino-control-fields .typecho-option:has(textarea){grid-column:auto}.lumino-structured-row{grid-template-columns:1fr}.lumino-structured-row .lumino-icon-button{grid-row:1;grid-column:1;justify-self:end}.lumino-image-editor{grid-template-columns:46px minmax(0,1fr)}.lumino-image-preview{width:46px;height:46px}.lumino-color-editor{grid-template-columns:46px minmax(0,1fr)}.lumino-color-editor input[type=color]{width:46px}.lumino-color-editor output,.lumino-color-help{display:none}.lumino-about-card{grid-template-columns:1fr}.lumino-about-item.is-wide{grid-column:auto}}
</style>
<script>
(function () {
  function initLuminoControlCenter() {
    var form = document.querySelector('.typecho-page-main form');
    if (!form || form.dataset.luminoReady) return;
    document.body.classList.add('lumino-admin-wide');
    form.dataset.luminoReady = '1';
    form.classList.add('lumino-settings');

    var groups = [
      { id: 'site', label: '网站', title: '网站信息', text: '管理名称、描述、备案信息与作者资料。', fields: ['siteName','siteTagline','siteDescription','beianNumber','faviconUrl','logoUrl','authorName','authorBio','authorAvatar'] },
      { id: 'home', label: '首页', title: '首页内容', text: '控制封面、按钮、模块标题、近期文章、分类与侧栏的呈现。', fields: ['heroEnabled','heroKicker','heroTitle','heroSubtitle','heroActionText','heroImage','heroOverlay','recentKicker','recentTitle','readMoreText','showCategories','categoryKicker','categoryTitle','categoryIntro','categoryLayout','categoryLimit','categorySettings','showSidebar'] },
      { id: 'nav', label: '导航', title: '导航与页面', text: '页面链接会自动读取 Typecho 当前伪静态生成的真实地址。', fields: ['navLinks','appendPages','archivePageSlug','archivePostLimit','aboutPageSlug','footerText','socialLinks'] },
      { id: 'reading', label: '阅读', title: '阅读体验', text: '文章目录、进度、签到及回到顶部等轻量功能。', fields: ['showToc','showReadingTime','showReadingProgress','showBackTop','showCheckin','checkinLabel','checkinNote','excerptLength'] },
      { id: 'style', label: '外观', title: '外观细节', text: '默认使用淡白纸面与淡薄荷色，可在这里微调。', fields: ['blurIntensity','accentColor','paperColor','surfaceColor'] },
      { id: 'advanced', label: '高级', title: '高级设置', text: '仅在需要统计、验证或小范围样式覆盖时使用。', fields: ['customHead','customCss'] },
      { id: 'about', label: '关于', title: '关于 Lumino', text: '项目与作者信息。', fields: [], about: true }
    ];
    var shell = document.createElement('section');
    shell.id = 'lumino-control-center';
    shell.innerHTML = '<header class="lumino-control-header"><div><p class="lumino-control-kicker">LUMINO / 26.0</p><h2>主题控制台</h2></div><p>所有设置保存到当前主题，不修改 Typecho 的功能、路由或数据库结构。</p></header><div class="lumino-control-body"><nav class="lumino-control-menu" aria-label="主题设置分组"></nav><div class="lumino-control-panels"></div></div>';
    form.insertBefore(shell, form.firstChild);
    var menu = shell.querySelector('.lumino-control-menu');
    var panels = shell.querySelector('.lumino-control-panels');
    var fieldRows = {};
    Array.prototype.forEach.call(form.querySelectorAll('.typecho-option'), function (row) {
      var input = row.querySelector('[name]');
      if (input && input.name) fieldRows[input.name] = row;
    });

    function selectGroup(id) {
      Array.prototype.forEach.call(menu.querySelectorAll('button'), function (button) { button.classList.toggle('is-current', button.dataset.target === id); });
      Array.prototype.forEach.call(panels.querySelectorAll('.lumino-control-panel'), function (panel) { panel.classList.toggle('is-current', panel.dataset.panel === id); });
    }

    groups.forEach(function (group, index) {
      var button = document.createElement('button');
      button.type = 'button';
      button.textContent = group.label;
      button.dataset.target = group.id;
      button.addEventListener('click', function () { selectGroup(group.id); });
      menu.appendChild(button);

      var panel = document.createElement('section');
      panel.className = 'lumino-control-panel';
      panel.dataset.panel = group.id;
      panel.innerHTML = '<div class="lumino-control-panel-header"><h3></h3><p></p></div><div class="lumino-control-fields"></div>';
      panel.querySelector('h3').textContent = group.title;
      panel.querySelector('p').textContent = group.text;
      var fields = panel.querySelector('.lumino-control-fields');
      if (group.about) {
        fields.innerHTML = '<div class="lumino-about-card"><div class="lumino-about-item"><small>作者</small><strong>曦芒</strong><p>Lumino 轻语博客主题的设计与开发。</p></div><div class="lumino-about-item"><small>项目</small><strong>Lumino 轻语博客 26.0</strong><p>现代、克制的 Typecho 写作主题，提供淡薄荷视觉、可视化主题设置与本地图片上传。</p></div><div class="lumino-about-item"><small>QQ 用户交流群</small><strong><a href="https://qm.qq.com/q/rflUXn2hPy" target="_blank" rel="noopener">加入 QQ 用户交流群</a></strong><p>反馈问题、交流配置与获取主题更新信息。</p></div><div class="lumino-about-item"><small>作者 QQ</small><strong>3981684967</strong><p>用于主题相关咨询与合作联系。</p></div><div class="lumino-about-item is-wide"><small>作者邮箱</small><strong><a href="mailto:shiyeyunya@vip.qq.com">shiyeyunya@vip.qq.com</a></strong><p>欢迎发送使用反馈与改进建议。</p></div></div>';
      } else {
        group.fields.forEach(function (name) { if (fieldRows[name]) fields.appendChild(fieldRows[name]); });
      }
      panels.appendChild(panel);
      if (index === 0) selectGroup(group.id);
    });

    var uploadUrl = __LUMINO_UPLOAD_URL__;

    function notifySource(source) {
      source.dispatchEvent(new Event('input', { bubbles: true }));
      source.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function makeStructuredEditor(config) {
      var source = form.querySelector('[name="' + config.name + '"]');
      if (!source) return;
      var sourceRow = source.closest('.typecho-option');
      if (!sourceRow) return;
      sourceRow.classList.add('lumino-managed-source');

      var editor = document.createElement('section');
      editor.className = 'lumino-structured-editor';
      editor.innerHTML = '<div class="lumino-structured-heading"><div><strong></strong><small></small></div></div><div class="lumino-structured-list"></div><button class="lumino-add-row" type="button"></button>';
      editor.querySelector('strong').textContent = config.title;
      editor.querySelector('small').textContent = config.description;
      editor.querySelector('.lumino-add-row').textContent = '+ 添加' + config.itemLabel;
      sourceRow.parentNode.insertBefore(editor, sourceRow.nextSibling);
      var list = editor.querySelector('.lumino-structured-list');

      function sync() {
        var lines = [];
        Array.prototype.forEach.call(list.querySelectorAll('.lumino-structured-row'), function (row) {
          var values = [];
          Array.prototype.forEach.call(row.querySelectorAll('[data-lumino-value]'), function (input) { values.push(input.value.trim()); });
          if (values.some(function (value) { return value !== ''; })) lines.push(values.join('|'));
        });
        source.value = lines.join('\n');
        notifySource(source);
      }

      function addRow(values) {
        var row = document.createElement('div');
        row.className = 'lumino-structured-row';
        row.style.setProperty('--lumino-columns', config.fields.length);
        config.fields.forEach(function (field, index) {
          var input = document.createElement('input');
          input.type = field.type || 'text';
          input.placeholder = field.placeholder;
          input.value = values && values[index] ? values[index] : (field.defaultValue || '');
          input.setAttribute('data-lumino-value', '');
          input.addEventListener('input', sync);
          input.addEventListener('change', sync);
          row.appendChild(input);
        });
        var remove = document.createElement('button');
        remove.className = 'lumino-icon-button';
        remove.type = 'button';
        remove.textContent = '×';
        remove.title = '删除' + config.itemLabel;
        remove.addEventListener('click', function () { row.remove(); sync(); });
        row.appendChild(remove);
        list.appendChild(row);
      }

      var rows = source.value.split(/\r?\n/).filter(function (line) { return line.trim() !== ''; });
      rows.forEach(function (line) { addRow(line.split('|')); });
      if (!rows.length && config.defaultRow) addRow(config.defaultRow);
      editor.querySelector('.lumino-add-row').addEventListener('click', function () { addRow(config.emptyRow || []); sync(); });
    }

    [
      { name: 'navLinks', title: '导航菜单', description: '添加项目后，使用 home 或 page:页面别名；页面链接会自动适配伪静态。', itemLabel: '菜单项', defaultRow: ['首页', 'home'], fields: [{ placeholder: '显示名称' }, { placeholder: 'home 或 page:about' }] },
      { name: 'socialLinks', title: '页脚社交链接', description: '添加需要显示在页脚的外部链接或 RSS 订阅地址。', itemLabel: '社交链接', fields: [{ placeholder: '名称，例如 GitHub' }, { placeholder: 'https://...' }] },
      { name: 'categorySettings', title: '分类卡片', description: '每个分类可设置显示名称、简介与强调色。', itemLabel: '分类', fields: [{ placeholder: '分类别名' }, { placeholder: '显示名称' }, { placeholder: '分类简介' }, { type: 'color', placeholder: '强调色', defaultValue: '#9ccfbc' }] }
    ].forEach(makeStructuredEditor);

    function makeColorEditor(name, fallback) {
      var source = form.querySelector('[name="' + name + '"]');
      if (!source) return;
      var sourceRow = source.closest('.typecho-option');
      if (!sourceRow) return;
      source.classList.add('lumino-color-source');
      var value = /^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(source.value.trim()) ? source.value.trim() : fallback;
      var editor = document.createElement('div');
      editor.className = 'lumino-color-editor';
      editor.innerHTML = '<input type="color" aria-label="选择颜色"><input type="text" inputmode="text" spellcheck="false" aria-label="HEX 颜色值"><output aria-hidden="true"></output>';
      var picker = editor.querySelector('input[type=color]');
      var text = editor.querySelector('input[type=text]');
      var swatch = editor.querySelector('output');
      picker.value = value;
      text.value = value;
      swatch.style.backgroundColor = value;
      source.style.display = 'none';
      source.parentNode.insertBefore(editor, source.nextSibling);

      function sync(value) {
        if (!/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(value)) return false;
        source.value = value;
        picker.value = value;
        text.value = value;
        swatch.style.backgroundColor = value;
        notifySource(source);
        return true;
      }
      picker.addEventListener('input', function () { sync(picker.value); });
      text.addEventListener('input', function () {
        var next = text.value.trim();
        if (/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i.test(next)) sync(next);
      });
      text.addEventListener('blur', function () {
        if (!sync(text.value.trim())) text.value = source.value || fallback;
      });
    }

    [
      { name: 'accentColor', fallback: '#9ccfbc' },
      { name: 'paperColor', fallback: '#fbfdfc' },
      { name: 'surfaceColor', fallback: '#f1faf6' }
    ].forEach(function (config) { makeColorEditor(config.name, config.fallback); });

    function makeImageEditor(name) {
      var source = form.querySelector('[name="' + name + '"]');
      if (!source) return;
      var sourceRow = source.closest('.typecho-option');
      if (!sourceRow) return;
      var title = sourceRow.querySelector('.typecho-label');
      var editor = document.createElement('div');
      editor.className = 'lumino-image-editor';
      editor.innerHTML = '<div class="lumino-image-preview">预览</div><div><strong></strong><div class="lumino-image-actions"><label class="lumino-upload-button">本地上传<input type="file" accept="image/*" hidden></label><span class="lumino-upload-status">可粘贴链接或上传图片</span></div></div>';
      editor.querySelector('strong').textContent = title ? title.textContent : '图片资源';
      sourceRow.appendChild(editor);
      var preview = editor.querySelector('.lumino-image-preview');
      var status = editor.querySelector('.lumino-upload-status');
      var fileInput = editor.querySelector('input[type=file]');

      function setPreview(url) {
        preview.innerHTML = '';
        if (url) {
          var image = document.createElement('img');
          image.src = url;
          image.alt = '';
          image.onerror = function () { preview.textContent = '无预览'; };
          preview.appendChild(image);
        } else {
          preview.textContent = '预览';
        }
      }

      setPreview(source.value);
      source.addEventListener('input', function () { setPreview(source.value); });
      fileInput.addEventListener('change', function () {
        var file = fileInput.files && fileInput.files[0];
        if (!file) return;
        if (!/^image\//.test(file.type)) {
          status.textContent = '请选择图片文件';
          return;
        }
        if (!uploadUrl) {
          status.textContent = '上传接口不可用，请先保存后刷新页面';
          return;
        }
        status.textContent = '正在上传…';
        setPreview(URL.createObjectURL(file));
        var data = new FormData();
        data.append('file', file);
        fetch(uploadUrl, { method: 'POST', body: data, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(function (response) { if (!response.ok) throw new Error('upload failed'); return response.json(); })
          .then(function (result) {
            var attachment = Array.isArray(result) ? result[1] : null;
            var url = attachment && attachment.url ? attachment.url : (Array.isArray(result) ? result[0] : '');
            if (!url) throw new Error('missing url');
            source.value = url;
            setPreview(url);
            status.textContent = '上传完成';
            notifySource(source);
          })
          .catch(function () { status.textContent = '上传失败，请检查附件类型和权限'; });
      });
    }

    ['logoUrl', 'heroImage', 'authorAvatar', 'faviconUrl'].forEach(makeImageEditor);

    var summary = document.createElement('div');
    summary.className = 'lumino-control-summary';
    summary.innerHTML = '<strong>设置会随主题保存</strong>导航页面、签到与阅读组件均可单独关闭。';
    menu.appendChild(summary);

    var saveBar = document.createElement('div');
    saveBar.className = 'lumino-control-save';
    saveBar.innerHTML = '<small data-lumino-save-status>所有改动尚未保存</small>';
    var submitRow = form.querySelector('.typecho-option-submit');
    if (submitRow) saveBar.appendChild(submitRow);
    shell.appendChild(saveBar);
    form.addEventListener('input', function () { var status = form.querySelector('[data-lumino-save-status]'); if (status) status.textContent = '有未保存的改动'; });
    form.addEventListener('change', function () { var status = form.querySelector('[data-lumino-save-status]'); if (status) status.textContent = '有未保存的改动'; });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initLuminoControlCenter); else initLuminoControlCenter();
})();
</script>
HTML
    ));
    $form->addItem($panel);
}

function themeConfig($form)
{
    $siteName = new \Typecho\Widget\Helper\Form\Element\Text('siteName', null, 'Lumino 轻语博客', _t('网站名称'), _t('显示在导航栏、页脚和浏览器标题中。'));
    $form->addInput($siteName);

    $siteTagline = new \Typecho\Widget\Helper\Form\Element\Text('siteTagline', null, '记录灵感，也记录生活', _t('网站副标题'));
    $form->addInput($siteTagline);

    $siteDescription = new \Typecho\Widget\Helper\Form\Element\Textarea('siteDescription', null, '一个留白充足的写作空间，让每一次记录都清晰、安静而有光。', _t('网站描述'), _t('用于首页介绍和搜索引擎摘要。'));
    $form->addInput($siteDescription);

    $beian = new \Typecho\Widget\Helper\Form\Element\Text('beianNumber', null, '', _t('ICP备案号'), _t('填写后显示在网站页脚，例如：粤ICP备12345678号；留空则不显示。'));
    $form->addInput($beian);

    $favicon = new \Typecho\Widget\Helper\Form\Element\Text('faviconUrl', null, '', _t('网站图标地址'), _t('留空时使用主题 Logo。'));
    $form->addInput($favicon);

    $logo = new \Typecho\Widget\Helper\Form\Element\Text('logoUrl', null, '', _t('Logo 图片地址'), _t('留空时显示品牌首字母标记。'));
    $form->addInput($logo);

    $heroTitle = new \Typecho\Widget\Helper\Form\Element\Text('heroTitle', null, '把思绪放轻，把表达放远。', _t('首页主标题'));
    $form->addInput($heroTitle);

    $heroSubtitle = new \Typecho\Widget\Helper\Form\Element\Textarea('heroSubtitle', null, 'Lumino 是一个留白充足的写作空间，让每一次记录都清晰、安静而有光。', _t('首页介绍'));
    $form->addInput($heroSubtitle);

    $heroImage = new \Typecho\Widget\Helper\Form\Element\Text('heroImage', null, '', _t('首页视觉图片地址'), _t('留空时使用主题内置的森林车站封面图。'));
    $form->addInput($heroImage);

    $heroEnabled = new \Typecho\Widget\Helper\Form\Element\Radio('heroEnabled', array('1' => _t('显示'), '0' => _t('隐藏')), '1', _t('首页头图区域'));
    $form->addInput($heroEnabled);

    $heroKicker = new \Typecho\Widget\Helper\Form\Element\Text('heroKicker', null, 'LUMINO · 轻语博客', _t('首页封面眉题'));
    $form->addInput($heroKicker);

    $heroActionText = new \Typecho\Widget\Helper\Form\Element\Text('heroActionText', null, '开始阅读', _t('首页封面按钮文字'));
    $form->addInput($heroActionText);

    $author = new \Typecho\Widget\Helper\Form\Element\Text('authorName', null, 'Lumino Editor', _t('作者名称'));
    $form->addInput($author);

    $bio = new \Typecho\Widget\Helper\Form\Element\Textarea('authorBio', null, '写下值得被记住的片段。', _t('作者简介'));
    $form->addInput($bio);

    $avatar = new \Typecho\Widget\Helper\Form\Element\Text('authorAvatar', null, '', _t('作者头像地址'));
    $form->addInput($avatar);

    $nav = new \Typecho\Widget\Helper\Form\Element\Textarea('navLinks', null, "首页|home\n归档|page:archives\n关于|page:about", _t('导航链接'), _t('每行一个。使用 home 代表首页；使用 page:页面别名 可自动适配 Typecho 伪静态，例如：关于|page:about。'));
    $form->addInput($nav);

    $appendPages = new \Typecho\Widget\Helper\Form\Element\Radio('appendPages', array('1' => _t('自动追加'), '0' => _t('仅显示手动导航')), '1', _t('独立页面导航'), _t('自动将未在导航中配置的已发布独立页面追加到导航栏。'));
    $form->addInput($appendPages);

    $archivePageSlug = new \Typecho\Widget\Helper\Form\Element\Text('archivePageSlug', null, 'archives', _t('归档页面别名'), _t('创建一个对应别名的独立页面后，主题会自动生成符合伪静态设置的归档链接。'));
    $form->addInput($archivePageSlug);

    $archivePostLimit = new \Typecho\Widget\Helper\Form\Element\Select('archivePostLimit', array('30' => '30', '60' => '60', '100' => '100', '200' => '200'), '100', _t('归档文章数量'), _t('应用“归档页面”模板时显示的最新文章数量。'));
    $form->addInput($archivePostLimit);

    $aboutPageSlug = new \Typecho\Widget\Helper\Form\Element\Text('aboutPageSlug', null, 'about', _t('关于页面别名'), _t('创建一个对应别名的独立页面后，主题会自动生成符合伪静态设置的关于链接。'));
    $form->addInput($aboutPageSlug);

    $recentKicker = new \Typecho\Widget\Helper\Form\Element\Text('recentKicker', null, '最近更新', _t('近期文章眉题'));
    $form->addInput($recentKicker);

    $recentTitle = new \Typecho\Widget\Helper\Form\Element\Text('recentTitle', null, '近期文章', _t('近期文章标题'));
    $form->addInput($recentTitle);

    $readMoreText = new \Typecho\Widget\Helper\Form\Element\Text('readMoreText', null, '阅读全文', _t('近期文章按钮文字'));
    $form->addInput($readMoreText);

    $showCategories = new \Typecho\Widget\Helper\Form\Element\Radio('showCategories', array('1' => _t('显示'), '0' => _t('隐藏')), '1', _t('首页分类导航'));
    $form->addInput($showCategories);

    $categoryKicker = new \Typecho\Widget\Helper\Form\Element\Text('categoryKicker', null, '分类', _t('分类模块眉题'));
    $form->addInput($categoryKicker);

    $categoryTitle = new \Typecho\Widget\Helper\Form\Element\Text('categoryTitle', null, '按主题浏览', _t('分类区标题'));
    $form->addInput($categoryTitle);

    $categoryIntro = new \Typecho\Widget\Helper\Form\Element\Text('categoryIntro', null, '从不同角度，回看写下的片段。', _t('分类区说明'));
    $form->addInput($categoryIntro);

    $categoryLayout = new \Typecho\Widget\Helper\Form\Element\Select('categoryLayout', array('grid' => _t('网格卡片'), 'list' => _t('横向列表')), 'grid', _t('分类区布局'));
    $form->addInput($categoryLayout);

    $categoryLimit = new \Typecho\Widget\Helper\Form\Element\Select('categoryLimit', array('3' => '3', '6' => '6', '9' => '9', '0' => _t('全部')), '6', _t('分类显示数量'));
    $form->addInput($categoryLimit);

    $categorySettings = new \Typecho\Widget\Helper\Form\Element\Textarea('categorySettings', null, "default|随笔|记录思考与日常|#9ccfbc\ntech|技术|工具、代码与产品观察|#a7d7cb\ndesign|设计|审美、界面与灵感|#bfdacb", _t('分类设置项'), _t('每行一个，格式：分类别名|显示名称|分类简介|强调色。别名需与 Typecho 分类别名一致。'));
    $form->addInput($categorySettings);

    $sidebar = new \Typecho\Widget\Helper\Form\Element\Radio('showSidebar', array('1' => _t('显示'), '0' => _t('隐藏')), '1', _t('显示侧栏'));
    $form->addInput($sidebar);

    $toc = new \Typecho\Widget\Helper\Form\Element\Radio('showToc', array('1' => _t('显示'), '0' => _t('隐藏')), '1', _t('文章目录'));
    $form->addInput($toc);

    $reading = new \Typecho\Widget\Helper\Form\Element\Radio('showReadingTime', array('1' => _t('显示'), '0' => _t('隐藏')), '1', _t('阅读时长'));
    $form->addInput($reading);

    $blur = new \Typecho\Widget\Helper\Form\Element\Select('blurIntensity', array('soft' => _t('柔和'), 'medium' => _t('标准'), 'strong' => _t('通透')), 'medium', _t('全局高斯模糊强度'));
    $form->addInput($blur);

    $accent = new \Typecho\Widget\Helper\Form\Element\Text('accentColor', null, '#9ccfbc', _t('强调色'), _t('填写 HEX 颜色，例如 #9ccfbc。默认使用淡薄荷色。'));
    $form->addInput($accent);

    $paper = new \Typecho\Widget\Helper\Form\Element\Text('paperColor', null, '#fbfdfc', _t('页面底色'), _t('填写 HEX 颜色，例如 #fbfdfc。'));
    $form->addInput($paper);

    $surface = new \Typecho\Widget\Helper\Form\Element\Text('surfaceColor', null, '#f1faf6', _t('内容底色'), _t('填写 HEX 颜色，例如 #f1faf6。'));
    $form->addInput($surface);

    $heroOverlay = new \Typecho\Widget\Helper\Form\Element\Select('heroOverlay', array('light' => _t('明亮'), 'balanced' => _t('平衡'), 'deep' => _t('深邃')), 'balanced', _t('首页封面遮罩'));
    $form->addInput($heroOverlay);

    $progress = new \Typecho\Widget\Helper\Form\Element\Radio('showReadingProgress', array('1' => _t('显示'), '0' => _t('隐藏')), '1', _t('阅读进度条'));
    $form->addInput($progress);

    $checkin = new \Typecho\Widget\Helper\Form\Element\Radio('showCheckin', array('1' => _t('显示'), '0' => _t('隐藏')), '1', _t('每日签到'), _t('仅在 Typecho「允许注册」开启时显示在首页侧栏作者信息下方。'));
    $form->addInput($checkin);

    $checkinLabel = new \Typecho\Widget\Helper\Form\Element\Text('checkinLabel', null, '今日签到', _t('签到按钮文字'));
    $form->addInput($checkinLabel);

    $checkinNote = new \Typecho\Widget\Helper\Form\Element\Text('checkinNote', null, '保存今天的一点心情', _t('签到提示文字'));
    $form->addInput($checkinNote);

    $footer = new \Typecho\Widget\Helper\Form\Element\Textarea('footerText', null, '保持好奇，保持温柔。', _t('页脚文字'));
    $form->addInput($footer);

    $social = new \Typecho\Widget\Helper\Form\Element\Textarea('socialLinks', null, "RSS|/feed\nGitHub|https://github.com/", _t('社交链接'), _t('每行一个，格式：名称|链接地址。'));
    $form->addInput($social);

    $backTop = new \Typecho\Widget\Helper\Form\Element\Radio('showBackTop', array('1' => _t('显示'), '0' => _t('隐藏')), '1', _t('返回顶部按钮'));
    $form->addInput($backTop);

    $excerpt = new \Typecho\Widget\Helper\Form\Element\Select('excerptLength', array('120' => '120', '180' => '180', '240' => '240', '320' => '320'), '180', _t('首页摘要长度'));
    $form->addInput($excerpt);

    $customHead = new \Typecho\Widget\Helper\Form\Element\Textarea('customHead', null, '', _t('自定义 Head 代码'), _t('可用于统计代码、验证标签或额外字体。'));
    $form->addInput($customHead);

    $custom = new \Typecho\Widget\Helper\Form\Element\Textarea('customCss', null, '', _t('自定义 CSS'), _t('可覆盖主题样式，保存后立即生效。'));
    $form->addInput($custom);

    lumino_admin_control_center($form);
}
