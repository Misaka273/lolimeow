/**
 * 导航栏设置页 UI — 顶部壳层 + 工作区收纳 + 拖拽性能优化
 */
(function () {
    'use strict';

    function buildTopBar(title, themeOptionsUrl, livePreviewLink) {
        var top = document.createElement('div');
        top.className = 'boxmoe-nav-menus-top options-top-bar';

        var actionsHtml = '<a class="el-button" href="' + themeOptionsUrl + '">返回主题设置</a>';

        if (livePreviewLink && livePreviewLink.href) {
            var config = window.boxmoeNavMenuConfig || {};
            var previewLabel = config.livePreviewLabel || livePreviewLink.textContent.trim();
            actionsHtml =
                '<a class="el-button boxmoe-nav-menus-live-preview" href="' + livePreviewLink.href + '">' +
                    previewLabel +
                '</a>' + actionsHtml;
        }

        top.innerHTML =
            '<div class="header-set-title">' +
                '<div class="themes-name"><span class="dashicons dashicons-menu-alt3"></span> ' + title + '</div>' +
                '<div class="boxmoe-nav-menus-top-actions">' + actionsHtml + '</div>' +
            '</div>';

        return top;
    }

    function initNavMenuShell() {
        var wrap = document.querySelector('#wpbody-content > .wrap');

        if (!wrap || wrap.classList.contains('boxmoe-nav-menus-page')) {
            return;
        }

        if (!wrap.querySelector('#nav-menus-frame') && !wrap.querySelector('#menu-locations-wrap')) {
            return;
        }

        var config = window.boxmoeNavMenuConfig || {};
        var themeOptionsUrl = config.themeOptionsUrl || (config.adminUrl || '') + 'admin.php?page=boxmoe_options';
        var livePreview = wrap.querySelector(':scope > .page-title-action');
        var workspaceChildren = [];
        var child = wrap.firstChild;

        while (child) {
            var next = child.nextSibling;

            if (child.tagName === 'H1' ||
                (child.classList && (child.classList.contains('wp-header-end') || child.classList.contains('page-title-action')))) {
                if (child.classList && child.classList.contains('page-title-action')) {
                    livePreview = child;
                }
                wrap.removeChild(child);
            } else {
                workspaceChildren.push(child);
            }

            child = next;
        }

        var workspace = document.createElement('div');
        workspace.className = 'boxmoe-nav-menus-workspace';
        var fragment = document.createDocumentFragment();

        workspaceChildren.forEach(function (node) {
            fragment.appendChild(node);
        });
        workspace.appendChild(fragment);

        wrap.classList.add('boxmoe-nav-menus-page');
        wrap.appendChild(buildTopBar('导航栏设置', themeOptionsUrl, livePreview));
        wrap.appendChild(workspace);
    }

    function bindMenuDragPerf($) {
        var $body = $(document.body);
        var $menuList = $('#menu-to-edit');

        if (!$menuList.length) {
            return;
        }

        $menuList.on('sortstart', function () {
            $body.addClass('is-menu-dragging');
        });

        $menuList.on('sortstop', function () {
            $body.removeClass('is-menu-dragging');
        });
    }

    function bootShell() {
        initNavMenuShell();
    }

    // capture 阶段尽早收纳 DOM，避免 WP 菜单脚本重复绑定
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootShell, { once: true, capture: true });
    } else {
        bootShell();
    }

    if (window.jQuery) {
        window.jQuery(bindMenuDragPerf);
    }
})();
