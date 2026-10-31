/* FranFast theme — small, dependency-free behaviours. */
(function () {
  'use strict';

  // Mobile menu toggle.
  var header = document.querySelector('[data-ff-header]');
  if (header) {
    var toggle = header.querySelector('.site-header__toggle');
    var label = header.querySelector('[data-ff-toggle-label]');
    var setOpen = function (open) {
      header.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (label) { label.textContent = open ? toggle.dataset.labelClose : toggle.dataset.labelOpen; }
      document.documentElement.classList.toggle('ff-menu-open', open);
    };
    toggle.addEventListener('click', function () {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && header.classList.contains('is-open')) { setOpen(false); toggle.focus(); }
    });
    // Close after choosing an in-page anchor.
    header.querySelectorAll('.site-nav a').forEach(function (a) {
      a.addEventListener('click', function () { setOpen(false); });
    });
    var mq = window.matchMedia('(min-width: 1200px)');
    var onChange = function () { if (mq.matches) { setOpen(false); } };
    if (mq.addEventListener) { mq.addEventListener('change', onChange); }
  }
  // Newsletter → Pardot Form Handler through a hidden iframe (see template-parts/newsletter.php).
  var form = document.querySelector('[data-ff-newsletter]');
  if (form) {
    var status = form.querySelector('[data-ff-nl-status]');
    var btn = form.querySelector('button[type="submit"]');
    var hp = form.querySelector('.newsletter__hp input');
    var name = form.querySelector('input[autocomplete="given-name"]');
    var last = form.querySelector('input[autocomplete="family-name"]');
    var email = form.querySelector('input[type="email"]');
    var pageField = form.querySelector('[data-ff-page-url]');
    var origins = [];
    try { origins = JSON.parse(form.dataset.origins || '[]'); } catch (e) {}
    var frame = document.createElement('iframe');
    frame.name = 'ff-nl-frame';
    frame.title = 'Newsletter';
    frame.hidden = true;
    frame.setAttribute('aria-hidden', 'true');
    frame.tabIndex = -1;
    document.body.appendChild(frame);
    form.target = 'ff-nl-frame';
    var timer = null;

    var show = function (state, msg) {
      status.dataset.state = state;
      status.textContent = msg;
      btn.disabled = state === 'sending';
      form.setAttribute('aria-busy', state === 'sending' ? 'true' : 'false');
    };
    var invalid = function (input, msg) {
      input.setAttribute('aria-invalid', 'true');
      show('error', msg);
      input.focus();
    };

    form.addEventListener('submit', function (e) {
      [name, last, email].forEach(function (i) { if (i) { i.removeAttribute('aria-invalid'); i.value = i.value.trim(); } });
      if (!name.value) { e.preventDefault(); invalid(name, form.dataset.msgName); return; }
      if (last && !last.value) { e.preventDefault(); invalid(last, form.dataset.msgLast); return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.value)) { e.preventDefault(); invalid(email, form.dataset.msgEmail); return; }
      if (hp && hp.value) { e.preventDefault(); form.reset(); show('ok', form.dataset.msgOk); return; } // Bot: pretend success, send nothing.
      if (form.hasAttribute('data-ff-preview')) { e.preventDefault(); show('error', form.dataset.msgError); if (window.console) { console.info('FranFast newsletter: set newsletter.action in config.json'); } return; }
      if (pageField) { pageField.value = window.location.href.split('#')[0]; }
      show('sending', '');
      clearTimeout(timer);
      timer = setTimeout(function () { show('error', form.dataset.msgError); }, 15000);
    });

    window.addEventListener('message', function (e) {
      if (origins.indexOf(e.origin) === -1 || !e.data || !e.data.ffNewsletter) { return; }
      clearTimeout(timer);
      if (e.data.ffNewsletter === 'ok') { form.reset(); show('ok', form.dataset.msgOk); }
      else { show('error', form.dataset.msgError); }
    });
  }
  // Home: FranFast AI agent demo (scenarios from lang/*.json → home.agent.scenarios).
  var agent = document.querySelector('[data-ff-agent]');
  if (agent) {
    var data = [];
    try { data = JSON.parse(agent.querySelector('[data-ff-agent-data]').textContent); } catch (e) {}
    var elStatus = agent.querySelector('[data-ff-agent-status]');
    var elPersona = agent.querySelector('[data-ff-agent-persona]');
    var elPrompt = agent.querySelector('[data-ff-agent-prompt]');
    var elLog = agent.querySelector('[data-ff-agent-log]');
    var dots = agent.querySelectorAll('[data-ff-agent-dot]');
    var autoplay = agent.dataset.autoplay !== '0';
    var checkSvg = (elLog.querySelector('svg') || {}).outerHTML || '';
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var s = 0, k = reduce ? 5 : 1, running = false, iv = null;

    var esc = function (t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; };
    var render = function () {
      var sc = data[s]; if (!sc) { return; }
      var shown = Math.min(k, 4), lines = [];
      for (var i = 0; i < shown; i++) {
        var done = k >= 5 || i < shown - 1;
        lines.push({ text: sc.steps[i], done: done });
      }
      var win = lines.slice(Math.max(0, lines.length - 3));
      elLog.innerHTML = win.map(function (l, idx) {
        var cls = 'agent-line ' + (l.done ? 'is-done' : 'is-working') + (win.length === 3 && idx === 0 ? ' is-faded' : '');
        var icon = l.done ? checkSvg : '<span class="pulse-dot ff-pulse" aria-hidden="true"></span>';
        return '<div class="' + cls + '">' + icon + '<span>' + esc(l.text) + '</span></div>';
      }).join('');
      elPersona.textContent = agent.dataset.askedBy.replace('%s', sc.persona);
      elPrompt.textContent = sc.prompt;
      elStatus.textContent = k >= 5 ? agent.dataset.done : agent.dataset.working;
      dots.forEach(function (d, i) { d.classList.toggle('is-active', i === s); d.setAttribute('aria-pressed', i === s ? 'true' : 'false'); });
    };
    var tick = function () {
      if (reduce) { s = (s + 1) % data.length; k = 5; }
      else if (k < 6) { k++; }
      else { s = (s + 1) % data.length; k = 1; }
      render();
    };
    var start = function () { if (autoplay && !running && data.length) { running = true; iv = setInterval(tick, reduce ? 9000 : 2600); } };
    var stop = function () { running = false; clearInterval(iv); };
    // Dots: jump to a scenario. The demo shows it complete, then autoplay (if on) continues from there.
    dots.forEach(function (d) {
      d.addEventListener('click', function () {
        var was = running;
        stop();
        s = parseInt(d.getAttribute('data-ff-agent-dot'), 10) || 0;
        k = 5;
        render();
        if (was || autoplay) { start(); }
      });
    });
    if (data.length) {
      if (!reduce) { if (!autoplay) { k = 5; } render(); }
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) { entries[0].isIntersecting ? start() : stop(); }).observe(agent);
      } else { start(); }
      document.addEventListener('visibilitychange', function () { if (document.hidden) { stop(); } });
    }
  }

  // Video cards (home "Ask FranFast", About), loaded only on click (config.json → home.video_url, about.video_url).
  document.querySelectorAll('[data-ff-video-play]').forEach(function (vbtn) {
    vbtn.addEventListener('click', function () {
      var box = vbtn.closest('[data-ff-video]'), url = box.dataset.ffVideo, el;
      var yt = url.match(/(?:youtu\.be\/|v=|embed\/)([\w-]{11})/), vm = url.match(/vimeo\.com\/(\d+)/);
      if (yt) { el = document.createElement('iframe'); el.src = 'https://www.youtube-nocookie.com/embed/' + yt[1] + '?autoplay=1&rel=0'; }
      else if (vm) { el = document.createElement('iframe'); el.src = 'https://player.vimeo.com/video/' + vm[1] + '?autoplay=1'; }
      else { el = document.createElement('video'); el.src = url; el.controls = true; el.autoplay = true; }
      if (el.tagName === 'IFRAME') { el.allow = 'autoplay; fullscreen; picture-in-picture'; el.allowFullscreen = true; el.title = vbtn.getAttribute('aria-label'); }
      box.appendChild(el);
    });
  });
  // Single post: copy link.
  document.querySelectorAll('[data-ff-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var done = function () { var st = b.parentNode.querySelector('.share__status'); if (st) { st.textContent = b.dataset.copied; setTimeout(function () { st.textContent = ''; }, 2500); } };
      if (navigator.clipboard) { navigator.clipboard.writeText(b.dataset.ffCopy).then(done); }
      else { var t = document.createElement('textarea'); t.value = b.dataset.ffCopy; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); done(); } catch (e) {} t.remove(); }
    });
  });
})();

// Add-ons filter (page-add-ons.php): filter in place; links still work without JS.
(function () {
  'use strict';
  var nav = document.querySelector('[data-ff-filter]');
  var list = document.querySelector('[data-ff-filter-list]');
  if (!nav || !list) { return; }
  nav.addEventListener('click', function (e) {
    var link = e.target.closest('a[data-filter]');
    if (!link) { return; }
    e.preventDefault();
    var value = link.getAttribute('data-filter');
    nav.querySelectorAll('a[data-filter]').forEach(function (a) {
      var on = a === link;
      a.classList.toggle('is-active', on);
      if (on) { a.setAttribute('aria-current', 'true'); } else { a.removeAttribute('aria-current'); }
    });
    list.querySelectorAll('[data-cat]').forEach(function (item) {
      item.hidden = !!value && item.getAttribute('data-cat') !== value;
    });
    if (window.history && history.replaceState) { history.replaceState(null, '', link.href); }
  });
})();

// Scroll reveal: sections and cards fade up once when they enter the viewport (CSS in main.css → Motion).
// Skipped with prefers-reduced-motion or without IntersectionObserver; content is always visible without JS.
(function () {
  'use strict';
  if (!document.body.classList.contains('ff-motion')) { return; } // config.json → motion.enabled
  if (!('IntersectionObserver' in window) || !window.matchMedia('(prefers-reduced-motion: no-preference)').matches) { return; }
  var selector = [
    'main .section-head', 'main .section > .container > .h2', 'main .split__main', 'main .split__side',
    'main .grid > li', 'main .addon-grid > li', 'main .review-wall > li', 'main .post-grid > li',
    'main .about-points > li', 'main .stat-grid > *', 'main .steps > *', 'main .faq__item', 'main .cta-band__inner'
  ].join(',');
  var items = Array.prototype.filter.call(document.querySelectorAll(selector), function (el) {
    return !el.closest('.hero, .listing-head, .ty-hero, [data-ff-reveal]');
  });
  if (!items.length) { return; }
  items.forEach(function (el) {
    el.setAttribute('data-ff-reveal', '');
    var siblings = el.parentNode ? Array.prototype.indexOf.call(el.parentNode.children, el) : 0;
    el.style.setProperty('--ff-i', String(Math.min(siblings, 5)));
  });
  document.documentElement.classList.add('ff-reveal');
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) { return; }
      var el = entry.target;
      el.classList.add('is-in');
      io.unobserve(el);
      // Once shown, hand the element back to its normal styles so hover effects have no reveal delay.
      var done = function () { el.removeAttribute('data-ff-reveal'); el.classList.remove('is-in'); el.style.removeProperty('--ff-i'); };
      el.addEventListener('transitionend', function handler(e) { if (e.target === el && e.propertyName === 'transform') { el.removeEventListener('transitionend', handler); done(); } });
      setTimeout(done, 1400); // Fallback if transitionend does not fire.
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
  items.forEach(function (el) { io.observe(el); });
})();

// Home client logos on mobile: swipeable strip that loops and drifts slowly (CSS: "Home client logos").
// Clones are decorative (aria-hidden, empty alt). Drift only with motion on (config.json → motion.enabled)
// and without prefers-reduced-motion; swiping and the loop always work.
(function () {
  'use strict';
  var list = document.querySelector('[data-ff-marquee]');
  if (!list) { return; }
  Array.prototype.slice.call(list.children).forEach(function (li) {
    var c = li.cloneNode(true);
    c.classList.add('is-clone');
    c.setAttribute('aria-hidden', 'true');
    c.querySelectorAll('img').forEach(function (img) { img.alt = ''; img.loading = 'eager'; });
    list.appendChild(c);
  });
  var mq = window.matchMedia('(max-width: 767px)');
  var drift = document.body.classList.contains('ff-motion') && window.matchMedia('(prefers-reduced-motion: no-preference)').matches;
  var pos = 0, raf = null, hold = false, resume = null, visible = true;
  var half = function () { var first = list.querySelector('.is-clone'); return first ? first.offsetLeft - list.firstElementChild.offsetLeft : 0; };
  var wrap = function () {
    var h = half(); if (!h) { return; }
    if (list.scrollLeft >= h) { list.scrollLeft -= h; } else if (list.scrollLeft <= 0) { list.scrollLeft += h; }
    pos = list.scrollLeft;
  };
  var step = function () {
    raf = null;
    if (!mq.matches || hold || !visible) { return; }
    pos += 0.35;
    list.scrollLeft = pos;
    if (list.scrollLeft >= half()) { wrap(); }
    raf = requestAnimationFrame(step);
  };
  var play = function () { if (drift && !raf) { pos = list.scrollLeft; raf = requestAnimationFrame(step); } };
  var pause = function () { hold = true; clearTimeout(resume); if (raf) { cancelAnimationFrame(raf); raf = null; } };
  var later = function () { clearTimeout(resume); resume = setTimeout(function () { hold = false; play(); }, 2500); };
  list.addEventListener('pointerdown', pause);
  list.addEventListener('touchstart', pause, { passive: true });
  list.addEventListener('pointerup', later);
  list.addEventListener('touchend', later, { passive: true });
  list.addEventListener('scroll', function () { if (hold && mq.matches) { wrap(); } }, { passive: true });
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (e) { visible = e[0].isIntersecting; if (visible) { play(); } }).observe(list);
  }
  var setup = function () { if (mq.matches) { list.scrollLeft = 1; pos = 1; play(); } };
  if (mq.addEventListener) { mq.addEventListener('change', setup); }
  window.addEventListener('load', setup);
})();

// Counters: numbers with data-ff-count count up once when visible (Home enterprise stats).
// Without motion (config.json → motion.enabled false, or reduced motion) the final number simply shows.
(function () {
  'use strict';
  var els = document.querySelectorAll('[data-ff-count]');
  if (!els.length || !document.body.classList.contains('ff-motion') || !('IntersectionObserver' in window) ||
      !window.matchMedia('(prefers-reduced-motion: no-preference)').matches) { return; }
  var run = function (el) {
    var end = parseInt(el.getAttribute('data-ff-count'), 10) || 0, dur = 1400, t0 = null;
    var frame = function (t) {
      if (t0 === null) { t0 = t; }
      var p = Math.min(1, (t - t0) / dur), eased = 1 - Math.pow(1 - p, 3);
      el.textContent = String(Math.round(end * eased));
      if (p < 1) { requestAnimationFrame(frame); }
    };
    requestAnimationFrame(frame);
  };
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) { if (e.isIntersecting) { io.unobserve(e.target); run(e.target); } });
  }, { threshold: 0.5 });
  els.forEach(function (el) { el.style.minWidth = el.offsetWidth + 'px'; el.style.display = 'inline-block'; el.textContent = '0'; io.observe(el); });
})();

// Reading progress bar (header.php, config.json → motion.progress_bar). Works with reduced motion too: it only reflects the scroll position.
(function () {
  'use strict';
  var bar = document.querySelector('[data-ff-progress]');
  if (!bar) { return; }
  var ticking = false;
  var update = function () {
    ticking = false;
    var doc = document.documentElement, max = doc.scrollHeight - window.innerHeight;
    var p = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;
    bar.style.transform = 'scaleX(' + p.toFixed(4) + ')';
  };
  var onScroll = function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } };
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  window.addEventListener('load', update);
  update();
})();

// External links inside post/page content: open in a new tab (they then get the external icon from main.css).
(function () {
  'use strict';
  document.querySelectorAll('.post-content a[href], .prose a[href]').forEach(function (a) {
    if (a.hostname && a.hostname !== window.location.hostname && /^https?:$/.test(a.protocol)) {
      a.target = '_blank';
      var rel = (a.rel || '').split(/\s+/).filter(Boolean);
      if (rel.indexOf('noopener') === -1) { rel.push('noopener'); }
      a.rel = rel.join(' ');
    }
  });
})();
