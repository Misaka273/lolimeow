/**
 * 全局气泡风格提示
 * 替换浏览器原生 title 提示与表单验证气泡
 */
(function() {
  'use strict';

  var bubbleConfig = window.shirokiBubbleConfig || {};
  var bubbleStyle = bubbleConfig.style || 'normal';
  var fieldHintMsg = bubbleConfig.fieldHintMsg || '探索';
  var emptyValidationMsg = bubbleConfig.emptyValidationMsg || '请输入内容';
  var bubbleStyleClasses = [
    'bubble-style-normal',
    'bubble-style-border',
    'bubble-style-shadow',
    'bubble-style-lines',
    'bubble-style-glass'
  ];
  var tooltipEl = null;
  var arrowEl = null;
  var hideTimer = null;
  var currentTarget = null;
  var initialized = false;

  function getBubbleStyleClass() {
    if (bubbleStyleClasses.indexOf('bubble-style-' + bubbleStyle) !== -1) {
      return 'bubble-style-' + bubbleStyle;
    }

    return 'bubble-style-normal';
  }

  function applyBubbleStyleClass(element) {
    if (!element) {
      return;
    }

    bubbleStyleClasses.forEach(function(className) {
      element.classList.remove(className);
    });
    element.classList.add(getBubbleStyleClass());
  }

  function createTooltipElement() {
    if (tooltipEl || !document.body) {
      return;
    }

    tooltipEl = document.createElement('div');
    tooltipEl.className = 'shiroki-bubble-tooltip is-info is-top';
    tooltipEl.setAttribute('role', 'tooltip');
    tooltipEl.setAttribute('aria-hidden', 'true');
    applyBubbleStyleClass(tooltipEl);

    arrowEl = document.createElement('span');
    arrowEl.className = 'shiroki-bubble-tooltip__arrow';
    tooltipEl.appendChild(arrowEl);

    document.body.appendChild(tooltipEl);
  }

  function clearHideTimer() {
    if (hideTimer) {
      window.clearTimeout(hideTimer);
      hideTimer = null;
    }
  }

  function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
  }

  function isFieldVisible(field) {
    if (!field || field.disabled || field.type === 'hidden') {
      return false;
    }

    var style = window.getComputedStyle(field);
    if (style.display === 'none' || style.visibility === 'hidden') {
      return false;
    }

    var rect = field.getBoundingClientRect();
    return rect.width > 0 || rect.height > 0;
  }

  function getTooltipAnchor(target) {
    if (!target) {
      return null;
    }

    if (isFieldVisible(target)) {
      return target;
    }

    return target.closest('.comment-form-comment, .search-wrap, .input-group, label') || target;
  }

  function getTooltipGap(target) {
    if (target && target.matches && target.matches('input, textarea, select')) {
      return 12;
    }

    return 10;
  }

  function resolveTooltipPlacement(target, preferredPlacement) {
    if (!tooltipEl || !target) {
      return preferredPlacement === 'bottom' ? 'bottom' : 'top';
    }

    var anchor = getTooltipAnchor(target);
    if (!anchor) {
      return preferredPlacement === 'bottom' ? 'bottom' : 'top';
    }

    var targetRect = anchor.getBoundingClientRect();
    var tooltipRect = tooltipEl.getBoundingClientRect();
    var gap = getTooltipGap(target);
    var viewportPadding = 12;
    var needHeight = tooltipRect.height + gap;
    var spaceAbove = targetRect.top - viewportPadding;
    var spaceBelow = window.innerHeight - targetRect.bottom - viewportPadding;
    var preferred = preferredPlacement === 'bottom' ? 'bottom' : 'top';

    if (preferred === 'top') {
      if (spaceAbove >= needHeight) {
        return 'top';
      }

      if (spaceBelow >= needHeight) {
        return 'bottom';
      }

      return spaceBelow > spaceAbove ? 'bottom' : 'top';
    }

    if (spaceBelow >= needHeight) {
      return 'bottom';
    }

    if (spaceAbove >= needHeight) {
      return 'top';
    }

    return spaceAbove > spaceBelow ? 'top' : 'bottom';
  }

  function getPreferredFieldPlacement(field) {
    if (!field || !field.closest) {
      return 'top';
    }

    if (field.closest('header, .navbar, .boxmoe_header, .search-form, .mobile-search-form, .offcanvas')) {
      return 'bottom';
    }

    return 'top';
  }

  function positionTooltip(target, placement) {
    if (!tooltipEl || !target) {
      return;
    }

    var anchor = getTooltipAnchor(target);
    var resolvedPlacement = resolveTooltipPlacement(target, placement);
    var gap = getTooltipGap(target);

    tooltipEl.style.left = '0px';
    tooltipEl.style.top = '0px';
    tooltipEl.classList.remove('is-top', 'is-bottom');
    tooltipEl.classList.add(resolvedPlacement === 'bottom' ? 'is-bottom' : 'is-top');

    var targetRect = anchor.getBoundingClientRect();
    var tooltipRect = tooltipEl.getBoundingClientRect();
    var left = targetRect.left + (targetRect.width / 2) - (tooltipRect.width / 2);
    var top;

    if (resolvedPlacement === 'bottom') {
      top = targetRect.bottom + gap;
    } else {
      top = targetRect.top - tooltipRect.height - gap;
    }

    left = clamp(left, 12, window.innerWidth - tooltipRect.width - 12);
    top = clamp(top, 12, window.innerHeight - tooltipRect.height - 12);

    tooltipEl.style.left = left + 'px';
    tooltipEl.style.top = top + 'px';
  }

  function hideTooltip() {
    clearHideTimer();

    if (!tooltipEl) {
      return;
    }

    tooltipEl.classList.remove('is-visible');
    tooltipEl.setAttribute('aria-hidden', 'true');
    tooltipEl.style.opacity = '';
    tooltipEl.style.visibility = '';
    tooltipEl.style.transform = '';
    currentTarget = null;
  }

  function showTooltip(text, target, options) {
    options = options || {};

    if (!text || !target) {
      return;
    }

    createTooltipElement();

    if (!tooltipEl) {
      return;
    }

    clearHideTimer();
    currentTarget = target;

    var isPanel = options.panel === true || options.type === 'panel';
    var type = options.type === 'error' ? 'error' : 'info';
    var placement = options.placement || 'top';

    tooltipEl.classList.remove('is-info', 'is-error', 'is-panel');
    tooltipEl.classList.add(type === 'error' ? 'is-error' : 'is-info');
    if (isPanel) {
      tooltipEl.classList.add('is-panel');
    }
    applyBubbleStyleClass(tooltipEl);

    var textNode = tooltipEl.querySelector('[data-shiroki-tooltip-text]');
    if (!textNode) {
      textNode = document.createElement('span');
      textNode.setAttribute('data-shiroki-tooltip-text', '');
      tooltipEl.insertBefore(textNode, arrowEl);
    }
    textNode.textContent = text;
    tooltipEl.setAttribute('aria-hidden', 'false');
    tooltipEl.style.opacity = '1';
    tooltipEl.style.visibility = 'visible';
    tooltipEl.style.transform = 'translateY(0)';
    positionTooltip(target, placement);
    tooltipEl.classList.add('is-visible');

    window.requestAnimationFrame(function() {
      positionTooltip(target, placement);
    });

    if (options.autoHide) {
      hideTimer = window.setTimeout(hideTooltip, options.autoHide);
    }
  }

  function getValidationMessage(input) {
    var customMessage = input.getAttribute('data-shiroki-invalid-msg');

    if (customMessage && input.validity.valueMissing) {
      return customMessage;
    }

    if (input.validity.valueMissing) {
      if (input.type === 'checkbox' || input.type === 'radio') {
        return '请勾选此选项';
      }
      return emptyValidationMsg;
    }

    if (input.validity.typeMismatch) {
      if (input.type === 'email') {
        return '请输入有效的邮箱地址';
      }
      if (input.type === 'url') {
        return '请输入有效的网址';
      }
      return '输入格式不正确';
    }

    if (input.validity.tooShort) {
      return '输入内容过短';
    }

    if (input.validity.patternMismatch) {
      return '输入内容不符合要求';
    }

    return input.validationMessage || '输入内容无效';
  }

  function showValidationError(input, message, options) {
    options = options || {};

    if (!input) {
      return;
    }

    input.classList.add('shiroki-field-error');
    showTooltip(message || getValidationMessage(input), input, {
      type: 'error',
      placement: getPreferredFieldPlacement(input),
      autoHide: options.autoHide
    });

    if (options.focus !== false && document.activeElement !== input && typeof input.focus === 'function') {
      input.focus();
    }
  }

  function getFirstInvalidField(form) {
    var fields = form.querySelectorAll('input, textarea, select');
    var i;

    for (i = 0; i < fields.length; i++) {
      if (
        fields[i].willValidate &&
        !fields[i].disabled &&
        isFieldVisible(fields[i]) &&
        !fields[i].validity.valid
      ) {
        return fields[i];
      }
    }

    return null;
  }

  function syncGuestFieldValidation() {
    document.querySelectorAll('.guest-inputs').forEach(function(wrap) {
      var enabled = wrap.classList.contains('active') && window.getComputedStyle(wrap).display !== 'none';

      wrap.querySelectorAll('input, textarea, select').forEach(function(field) {
        field.disabled = !enabled;

        if (!enabled) {
          field.classList.remove('shiroki-field-error');
        }
      });
    });
  }

  function disableNativeValidation(root) {
    var scope = root || document;
    var forms = scope.querySelectorAll ? scope.querySelectorAll('form') : [];

    forms.forEach(function(form) {
      form.setAttribute('novalidate', 'novalidate');
    });
  }

  function handleSearchFormSubmit(form, event) {
    var searchInput = form.querySelector('.search-input, .mobile-search-input');
    var value;

    if (!searchInput) {
      return false;
    }

    if (!searchInput.hasAttribute('data-shiroki-invalid-msg')) {
      searchInput.setAttribute('data-shiroki-invalid-msg', '✍🏻请输入内容🤪');
    }

    value = searchInput.value.replace(/\s+/g, ' ').trim();
    if (value) {
      return false;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
    showValidationError(
      searchInput,
      searchInput.getAttribute('data-shiroki-invalid-msg') || '✍🏻请输入内容🤪',
      { autoHide: 3000 }
    );
    return true;
  }

  function handleFormSubmit(event) {
    var form = event.target;

    if (!form || form.tagName !== 'FORM') {
      return;
    }

    if (form.classList.contains('search-form') || form.classList.contains('mobile-search-form')) {
      handleSearchFormSubmit(form, event);
      return;
    }

    var invalidField = getFirstInvalidField(form);
    if (!invalidField) {
      return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
    showValidationError(invalidField, null, { autoHide: 3000 });
  }

  function handleInvalid(event) {
    event.preventDefault();
    event.stopImmediatePropagation();
    showValidationError(event.target, null, { autoHide: 3000 });
  }

  function isSearchField(field) {
    return !!(field && field.matches && field.matches('.search-input, .mobile-search-input'));
  }

  function getFieldHintText(field) {
    if (!field) {
      return '';
    }

    var customHint = field.getAttribute('data-shiroki-tip') ||
      field.getAttribute('data-shiroki-title');

    if (customHint) {
      return customHint;
    }

    if (isSearchField(field)) {
      return fieldHintMsg || '探索';
    }

    return '';
  }

  function shouldSkipFieldTooltip(field) {
    if (!field || field.hasAttribute('data-shiroki-field-bound')) {
      return true;
    }

    if (field.type === 'hidden' || field.type === 'submit' || field.type === 'button' || field.type === 'reset') {
      return true;
    }

    if (field.hasAttribute('data-bs-toggle') || field.hasAttribute('data-bs-original-title')) {
      return true;
    }

    return false;
  }

  function refreshFieldTooltip(field) {
    if (!isFieldVisible(field) || field.disabled) {
      return;
    }

    var hint = getFieldHintText(field);

    if (hint) {
      field.classList.remove('shiroki-field-error');
      showTooltip(hint, field, {
        type: 'info',
        placement: getPreferredFieldPlacement(field)
      });
      return;
    }

    if (field.willValidate && !field.validity.valid) {
      showTooltip(getValidationMessage(field), field, {
        type: 'error',
        placement: getPreferredFieldPlacement(field)
      });
      field.classList.add('shiroki-field-error');
    }
  }

  function maybeHideFieldTooltip(field) {
    if (document.activeElement === field) {
      return;
    }

    field.classList.remove('shiroki-field-error');

    if (currentTarget === field) {
      hideTooltip();
    }
  }

  function bindFieldTooltip(field) {
    if (shouldSkipFieldTooltip(field)) {
      return;
    }

    if (field.hasAttribute('title')) {
      field.setAttribute('data-shiroki-title', field.getAttribute('title'));
      field.removeAttribute('title');
    }

    field.setAttribute('data-shiroki-field-bound', 'true');

    field.addEventListener('mouseenter', function() {
      refreshFieldTooltip(field);
    });

    field.addEventListener('mouseleave', function() {
      maybeHideFieldTooltip(field);
    });

    field.addEventListener('focus', function() {
      refreshFieldTooltip(field);
    });

    field.addEventListener('blur', function() {
      field.classList.remove('shiroki-field-error');
      if (currentTarget === field) {
        hideTooltip();
      }
    });

    field.addEventListener('input', function() {
      if (field.validity.valid) {
        field.classList.remove('shiroki-field-error');
        if (currentTarget === field) {
          hideTooltip();
        }
        return;
      }

      if (document.activeElement === field) {
        refreshFieldTooltip(field);
      }
    });

    field.addEventListener('change', function() {
      if (field.validity.valid) {
        field.classList.remove('shiroki-field-error');
        if (currentTarget === field) {
          hideTooltip();
        }
      }
    });
  }

  function shouldSkipTitleElement(element) {
    if (!element || !element.getAttribute) {
      return true;
    }

    if (element.hasAttribute('data-shiroki-title-bound')) {
      return true;
    }

    if (element.hasAttribute('data-bs-toggle') || element.hasAttribute('data-bs-original-title')) {
      return true;
    }

    if (element.closest('.tooltip, .popover')) {
      return true;
    }

    return false;
  }

  function bindTitleTooltip(element) {
    if (shouldSkipTitleElement(element)) {
      return;
    }

    var title = element.getAttribute('title');
    if (!title) {
      return;
    }

    element.setAttribute('data-shiroki-title', title);
    element.removeAttribute('title');
    element.setAttribute('data-shiroki-title-bound', 'true');

    element.addEventListener('mouseenter', function() {
      showTooltip(element.getAttribute('data-shiroki-title'), element, { type: 'info' });
    });

    element.addEventListener('mouseleave', hideTooltip);

    element.addEventListener('focus', function() {
      showTooltip(element.getAttribute('data-shiroki-title'), element, { type: 'info' });
    });

    element.addEventListener('blur', hideTooltip);
  }

  function initFieldTooltips(root) {
    (root || document).querySelectorAll('input, textarea, select').forEach(bindFieldTooltip);
  }

  function normalizeExcerptText(text) {
    return (text || '').replace(/\s+/g, ' ').trim();
  }

  function isTruncatedExcerpt(element, fullText) {
    var full = normalizeExcerptText(fullText);
    var visible = normalizeExcerptText(element.textContent).replace(/\.\.\.$/, '');

    if (!full || !visible) {
      return false;
    }

    return full.length > visible.length + 2;
  }

  function bindPostCardExcerpt(element) {
    var fullText;

    if (!element || element.hasAttribute('data-shiroki-excerpt-bound')) {
      return;
    }

    fullText = element.getAttribute('data-shiroki-full-text');
    if (!isTruncatedExcerpt(element, fullText)) {
      return;
    }

    element.setAttribute('data-shiroki-excerpt-bound', 'true');
    element.classList.add('post-content-has-preview');
    element.setAttribute('tabindex', '0');

    function showExcerptPanel() {
      showTooltip(fullText, element, {
        type: 'info',
        panel: true,
        placement: 'top'
      });
    }

    function hideExcerptPanel() {
      if (currentTarget === element) {
        hideTooltip();
      }
    }

    element.addEventListener('mouseenter', function(event) {
      event.stopPropagation();
      showExcerptPanel();
    });

    element.addEventListener('mouseleave', hideExcerptPanel);

    element.addEventListener('focus', function(event) {
      event.stopPropagation();
      showExcerptPanel();
    });

    element.addEventListener('blur', hideExcerptPanel);
  }

  function initPostCardExcerptPopups(root) {
    (root || document).querySelectorAll('.post-list .post-content[data-shiroki-full-text]').forEach(bindPostCardExcerpt);
  }

  function initTitleTooltips(root) {
    (root || document).querySelectorAll('[title]').forEach(function(element) {
      if (element.matches('input, textarea, select')) {
        return;
      }
      bindTitleTooltip(element);
    });
  }

  function applyBubbleBodyClass() {
    if (!document.body) {
      return;
    }

    bubbleStyleClasses.forEach(function(className) {
      document.body.classList.remove(className.replace('bubble-style-', 'shiroki-bubble-style-'));
    });

    document.body.classList.add(getBubbleStyleClass().replace('bubble-style-', 'shiroki-bubble-style-'));
  }

  function initDomEnhancements() {
    if (initialized) {
      return;
    }

    initialized = true;
    applyBubbleBodyClass();
    createTooltipElement();
    disableNativeValidation(document);
    syncGuestFieldValidation();
    initTitleTooltips(document);
    initFieldTooltips(document);
    initPostCardExcerptPopups(document);

    document.addEventListener('click', function(event) {
      if (event.target.closest('.switch-account-btn')) {
        window.setTimeout(syncGuestFieldValidation, 0);
      }
    });

    window.addEventListener('scroll', function() {
      if (currentTarget && tooltipEl && tooltipEl.classList.contains('is-visible')) {
        positionTooltip(currentTarget, tooltipEl.classList.contains('is-bottom') ? 'bottom' : 'top');
      }
    }, true);

    window.addEventListener('resize', function() {
      if (currentTarget && tooltipEl && tooltipEl.classList.contains('is-visible')) {
        positionTooltip(currentTarget, tooltipEl.classList.contains('is-bottom') ? 'bottom' : 'top');
      }
    });

    if (typeof MutationObserver !== 'undefined' && document.body) {
      var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          mutation.addedNodes.forEach(function(node) {
            if (node.nodeType !== 1) {
              return;
            }

            if (node.tagName === 'FORM') {
              disableNativeValidation(node.parentNode || document);
            }

            if (node.matches && node.matches('[title]:not(input):not(textarea):not(select)')) {
              bindTitleTooltip(node);
            }

            if (node.matches && node.matches('input, textarea, select')) {
              bindFieldTooltip(node);
            }

            if (node.matches && node.matches('.post-content[data-shiroki-full-text]')) {
              bindPostCardExcerpt(node);
            }

            if (node.querySelectorAll) {
              disableNativeValidation(node);
              initTitleTooltips(node);
              initFieldTooltips(node);
              initPostCardExcerptPopups(node);
            }
          });
        });
      });

      observer.observe(document.body, { childList: true, subtree: true });
    }
  }

  document.addEventListener('invalid', handleInvalid, true);
  document.addEventListener('submit', handleFormSubmit, true);

  window.ShirokiBubbleTooltip = {
    show: showTooltip,
    hide: hideTooltip,
    showValidationError: showValidationError,
    syncGuestFieldValidation: syncGuestFieldValidation,
    getEmptyValidationMessage: function() {
      return emptyValidationMsg;
    },
    getFieldHintMessage: function() {
      return fieldHintMsg;
    },
    getSearchHintMessage: function() {
      return fieldHintMsg;
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDomEnhancements);
  } else {
    initDomEnhancements();
  }
})();
