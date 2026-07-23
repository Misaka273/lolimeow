/**
 * 文章内容分页：页码输入跳转
 */
(function() {
  'use strict';

  function clampPage(value, total) {
    var page = parseInt(value, 10);
    if (isNaN(page) || page < 1) {
      return 1;
    }
    if (page > total) {
      return total;
    }
    return page;
  }

  function initPageLinks(container) {
    var dataEl = container.querySelector('.page-links-data');
    var input = container.querySelector('.page-links-input');
    var btn = container.querySelector('.page-links-jump-btn');

    if (!dataEl || !input || !btn) {
      return;
    }

    var urls;
    try {
      urls = JSON.parse(dataEl.textContent);
    } catch (error) {
      return;
    }

    var total = parseInt(container.getAttribute('data-total'), 10) || 1;

    function jumpToPage() {
      var page = clampPage(input.value, total);
      input.value = page;

      var url = urls[String(page)];
      if (url) {
        window.location.href = url;
      }
    }

    btn.addEventListener('click', jumpToPage);
    input.addEventListener('keydown', function(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        jumpToPage();
      }
    });
  }

  function boot() {
    document.querySelectorAll('.page-links').forEach(initPageLinks);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
