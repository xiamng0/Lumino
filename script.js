(function () {
  var body = document.body;
  var menu = document.querySelector('.menu-toggle');
  var nav = document.querySelector('.site-nav');
  var searchToggle = document.querySelector('[data-search-toggle]');
  var searchPanel = document.querySelector('[data-search-panel]');
  var backTop = document.querySelector('[data-back-top]');
  var progress = document.querySelector('[data-reading-progress] span');

  function closeSearch() {
    if (!searchPanel || !searchToggle) return;
    searchPanel.classList.remove('is-open');
    searchToggle.setAttribute('aria-expanded', 'false');
  }

  if (menu && nav) {
    menu.addEventListener('click', function () {
      var open = body.classList.toggle('nav-open');
      menu.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  if (searchToggle && searchPanel) {
    searchToggle.addEventListener('click', function () {
      var open = searchPanel.classList.toggle('is-open');
      searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) searchPanel.querySelector('input').focus();
    });
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeSearch();
  });

  if (backTop) {
    backTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  function updateScrollTools() {
    var top = window.scrollY || document.documentElement.scrollTop;
    var height = document.documentElement.scrollHeight - window.innerHeight;
    if (progress) progress.style.transform = 'scaleX(' + (height > 0 ? Math.min(top / height, 1) : 0) + ')';
    if (backTop) backTop.classList.toggle('is-visible', top > 420);
  }

  window.addEventListener('scroll', updateScrollTools, { passive: true });
  updateScrollTools();

  function localDay(offset) {
    var date = new Date();
    date.setDate(date.getDate() + (offset || 0));
    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
  }

  var checkinButtons = Array.prototype.slice.call(document.querySelectorAll('[data-checkin]'));
  var streakLabel = document.querySelector('[data-checkin-streak]');
  var checkinKey = 'lumino-checkin-v1';

  function readCheckin() {
    try {
      return JSON.parse(window.localStorage.getItem(checkinKey) || '{}');
    } catch (error) {
      return {};
    }
  }

  function writeCheckin(value) {
    try {
      window.localStorage.setItem(checkinKey, JSON.stringify(value));
    } catch (error) {
      return false;
    }
    return true;
  }

  function renderCheckin() {
    var data = readCheckin();
    var signed = data.date === localDay();
    checkinButtons.forEach(function (button) {
      var label = button.querySelector('[data-checkin-label-text]') || button;
      var icon = button.querySelector('[data-checkin-icon]');
      label.textContent = signed ? '已签到' : (button.getAttribute('data-checkin-label') || '今日签到');
      if (icon) icon.textContent = signed ? '✓' : '+';
      button.classList.toggle('is-checked', signed);
      button.setAttribute('aria-pressed', signed ? 'true' : 'false');
    });
    if (streakLabel) streakLabel.textContent = data.streak ? '连续记录 ' + data.streak + ' 天' : '';
  }

  checkinButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      var data = readCheckin();
      if (data.date === localDay()) return;
      data = {
        date: localDay(),
        streak: data.date === localDay(-1) ? Number(data.streak || 0) + 1 : 1
      };
      writeCheckin(data);
      renderCheckin();
    });
  });
  renderCheckin();

  Array.prototype.forEach.call(document.querySelectorAll('[data-copy-link]'), function (button) {
    button.addEventListener('click', function () {
      var original = button.textContent;
      var done = function () {
        button.textContent = '已复制';
        window.setTimeout(function () { button.textContent = original; }, 1400);
      };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(window.location.href).then(done);
        return;
      }
      var field = document.createElement('textarea');
      field.value = window.location.href;
      field.setAttribute('readonly', '');
      field.style.position = 'fixed';
      field.style.opacity = '0';
      document.body.appendChild(field);
      field.select();
      document.execCommand('copy');
      field.remove();
      done();
    });
  });

  var content = document.querySelector('[data-reading-content]');
  var toc = document.querySelector('[data-toc]');
  if (!content || !toc) return;

  var headings = content.querySelectorAll('h2, h3');
  if (!headings.length) {
    toc.textContent = '暂无小节';
    return;
  }

  toc.textContent = '';
  var list = document.createElement('ol');
  var links = [];
  headings.forEach(function (heading, index) {
    var id = heading.id || 'section-' + (index + 1);
    heading.id = id;
    var item = document.createElement('li');
    var link = document.createElement('a');
    link.href = '#' + id;
    link.textContent = heading.textContent;
    item.appendChild(link);
    list.appendChild(item);
    links.push(link);
  });
  toc.appendChild(list);

  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        links.forEach(function (link) {
          link.classList.toggle('is-current', link.getAttribute('href') === '#' + entry.target.id);
        });
      });
    }, { rootMargin: '-18% 0px -72% 0px' });
    headings.forEach(function (heading) { observer.observe(heading); });
  }
})();
