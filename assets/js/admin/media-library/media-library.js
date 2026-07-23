/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 💎 拟态拟物玻璃质感媒体库UI设计 - JavaScript交互模块
 * 🎨 网格卡片式布局交互功能
 * 
 * @package Lolimeow_Shiroki
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 🎯 媒体库UI主对象
     */
    const ShirokiMediaLibrary = {
        
        /**
         * 📝 当前状态
         */
        state: {
            page: 1,
            loading: false,
            hasMore: true,
            filter: 'all',
            search: '',
            sort: 'date',
            order: 'desc',
            selectedItems: new Set(),
            perPage: 10 /* ◀️ 每页加载数量 */
        },

        /**
         * 🚀 初始化
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.hideOriginalUI();
            this.loadMediaItems();
        },

        /**
         * 📦 缓存DOM元素
         */
        cacheElements: function() {
            this.$grid = $('#shiroki-media-grid');
            this.$empty = $('#shiroki-media-empty');
            this.$search = $('#shiroki-media-search');
            this.$filters = $('.shiroki-media-filter-btn');
            this.$sortBtns = $('.shiroki-media-sort-btn');
            this.$originalTable = $('.wp-list-table');
            this.$originalNav = $('.tablenav');
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            const self = this;

            /* 🔍 搜索功能 - 搜索时加载所有结果，不受懒加载限制 */
            this.$search.on('input', this.debounce(function() {
                self.state.search = $(this).val();
                self.state.page = 1;
                self.state.hasMore = true;
                self.$grid.empty();
                self.loadMediaItems();
            }, 300));

            /* 🏷️ 筛选功能 */
            this.$filters.on('click', function() {
                const filter = $(this).data('filter');
                
                self.$filters.removeClass('active');
                $(this).addClass('active');
                
                self.state.filter = filter;
                self.state.page = 1;
                self.state.hasMore = true;
                self.$grid.empty();
                self.loadMediaItems();
            });

            /* 📜 无限滚动加载 - 每次滚动到底部加载数量 */
            $(window).on('scroll', this.debounce(function() {
                /* 🔍 搜索模式下不启用懒加载 */
                if (self.state.search) {
                    return;
                }
                if (self.isNearBottom() && !self.state.loading && self.state.hasMore) {
                    self.loadMediaItems();
                }
            }, 200));

            /* 🖱️ 卡片点击事件 - 切换选中状态 */
            this.$grid.on('click', '.shiroki-media-card', function(e) {
                // ◀️ 如果点击的是按钮或链接，不触发卡片点击
                if ($(e.target).closest('.shiroki-media-btn, .shiroki-media-label-url, a, button').length) {
                    return;
                }

                const id = parseInt($(this).data('id'));
                const $card = $(this);

                // ◀️ 切换选中状态
                if (self.state.selectedItems.has(id)) {
                    self.state.selectedItems.delete(id);
                    $card.removeClass('selected');
                } else {
                    self.state.selectedItems.add(id);
                    $card.addClass('selected');
                }

                self.updateBulkActions();
            });

            /* 📋 URL复制功能 */
            this.$grid.on('click', '.shiroki-media-label-url', function(e) {
                e.stopPropagation();
                const url = $(this).data('url');
                self.copyToClipboard(url, $(this));
            });

            /* 👁️ 查看按钮 */
            this.$grid.on('click', '.shiroki-media-btn-view', function(e) {
                e.stopPropagation();
                const url = $(this).data('url');
                window.open(url, '_blank');
            });

            /* ✏️ 编辑按钮 */
            this.$grid.on('click', '.shiroki-media-btn-edit', function(e) {
                e.stopPropagation();
                const link = $(this).data('link');
                window.location.href = link;
            });

            /* 🗑️ 删除按钮 */
            this.$grid.on('click', '.shiroki-media-btn-delete', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                const $card = $(this).closest('.shiroki-media-card');
                self.deleteMediaItem(id, $card);
            });

            /* 📦 批量操作按钮 */
            $(document).on('click', '.shiroki-media-bulk-btn', function(e) {
                e.preventDefault();
                const action = $(this).data('action');

                if (action === 'delete') {
                    self.bulkDelete();
                } else if (action === 'cancel') {
                    self.clearSelection();
                }
            });

            /* 🔽 排序按钮 */
            this.$sortBtns.on('click', function() {
                const sort = $(this).data('sort');
                let order = $(this).data('order');
                
                // ◀️ 如果点击的是当前激活的按钮，切换排序方向
                if ($(this).hasClass('active')) {
                    order = order === 'asc' ? 'desc' : 'asc';
                    $(this).data('order', order);
                }
                
                // ◀️ 更新按钮状态
                self.$sortBtns.removeClass('active');
                $(this).addClass('active');
                
                // ◀️ 更新排序图标
                self.$sortBtns.each(function() {
                    const btnSort = $(this).data('sort');
                    const btnOrder = $(this).data('order');
                    const icon = btnOrder === 'asc' ? '↑' : '↓';
                    $(this).attr('title', btnOrder === 'asc' ? '升序' : '降序');
                });
                
                // ◀️ 更新状态并重新加载
                self.state.sort = sort;
                self.state.order = order;
                self.state.page = 1;
                self.state.hasMore = true;
                self.$grid.empty();
                self.loadMediaItems();
            });
        },

        /**
         * 🙈 隐藏原版UI
         */
        hideOriginalUI: function() {
            // ◀️ 完全隐藏原版UI元素
            $('.view-switch, .wp-list-table, .tablenav, .media-toolbar, .attachments-wrapper, .attachments-browser, .wp-media-grid, .media-frame-content .attachments').hide();

            // ◀️ 移除主页面多余的空 media-frame-toolbar
            $('.wrap .media-frame-toolbar').remove();
            
            // ◀️ 显示自定义网格
            this.$grid.show();
        },

        /**
         * 📡 加载媒体项目
         */
        loadMediaItems: function() {
            if (this.state.loading || !this.state.hasMore) {
                return;
            }

            this.state.loading = true;
            this.showLoading();

            $.ajax({
                url: shirokiMediaConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_get_media_items',
                    nonce: shirokiMediaConfig.nonce,
                    page: this.state.page,
                    per_page: this.state.perPage, /* ◀️ 每页数量 */
                    search: this.state.search,
                    filter: this.state.filter,
                    sort: this.state.sort,
                    order: this.state.order
                },
                success: (response) => {
                    if (response.success) {
                        this.renderMediaItems(response.data.items);
                        this.state.hasMore = response.data.has_more;
                        this.state.page++;
                        
                        // ◀️ 显示/隐藏空状态
                        if (this.state.page === 2 && response.data.items.length === 0) {
                            this.$empty.show();
                            this.$grid.hide();
                        } else {
                            this.$empty.hide();
                            this.$grid.show();
                        }
                    } else {
                        this.$grid.html('<div class="shiroki-media-error">加载失败，请刷新页面重试</div>');
                    }
                },
                error: (xhr, status, error) => {
                    this.$grid.html('<div class="shiroki-media-error">加载失败: ' + error + '</div>');
                },
                complete: () => {
                    this.state.loading = false;
                    this.hideLoading();
                }
            });
        },

        /**
         * 🎨 渲染媒体项目
         */
        renderMediaItems: function(items) {
            if (!items || items.length === 0) {
                return;
            }

            const html = items.map(item => this.createMediaCard(item)).join('');
            this.$grid.append(html);
        },

        /**
         * 🃏 创建媒体卡片HTML
         */
        createMediaCard: function(item) {
            const isImage = item.file_type === 'image';
            const thumbnail = isImage && item.thumbnail 
                ? `<img src="${item.thumbnail}" alt="${this.escapeHtml(item.title)}">`
                : `<div class="shiroki-media-file-icon">${item.icon_svg}</div>`;
            
            const metaTags = [];
            if (item.dimensions) {
                metaTags.push(`<span class="shiroki-media-meta-tag shiroki-media-meta-dimensions">📐 ${item.dimensions}</span>`);
            }
            if (item.file_size) {
                metaTags.push(`<span class="shiroki-media-meta-tag shiroki-media-meta-size">📦 ${item.file_size}</span>`);
            }
            // ◀️ 🏷️ 格式勋章（文件扩展名）
            if (item.file_extension) {
                metaTags.push(`<span class="shiroki-media-meta-tag shiroki-media-meta-extension">${item.file_extension.toUpperCase()}</span>`);
            }
            metaTags.push(`<span class="shiroki-media-meta-tag shiroki-media-meta-type">${this.getFileTypeLabel(item.file_type)}</span>`);

            const description = item.caption || item.description || '暂无描述';
            const shortUrl = this.truncateUrl(item.url, 30);

            const isSelected = this.state.selectedItems.has(item.id);

            return `
                <div class="shiroki-media-card ${item.file_type === 'video' ? 'shiroki-media-type-video' : ''} ${isSelected ? 'selected' : ''}"
                     data-id="${item.id}"
                     data-type="${item.file_type}">

                    <!-- 🔘 选择圆圈（右上角） -->
                    <div class="shiroki-media-select-circle" data-id="${item.id}">
                        <div class="shiroki-media-select-inner"></div>
                    </div>

                    <!-- 🖼️ 缩略图区域 -->
                    <div class="shiroki-media-thumbnail">
                        ${thumbnail}
                        <!-- 🏷️ 元信息标签 -->
                        <div class="shiroki-media-meta">
                            ${metaTags.join('')}
                        </div>
                    </div>
                    
                    <!-- 📋 信息区域 -->
                    <div class="shiroki-media-info">
                        <!-- 🔵 文件名称标签 -->
                        <div class="shiroki-media-label-name">
                            <span class="shiroki-media-label-title">文件名称</span>
                            <span class="shiroki-media-label-content" title="${this.escapeHtml(item.title)}">
                                ${this.escapeHtml(item.title)}
                            </span>
                        </div>
                        
                        <!-- 🟢 文件描述标签 -->
                        <div class="shiroki-media-label-desc">
                            <span class="shiroki-media-label-title">文件描述</span>
                            <span class="shiroki-media-label-content" title="${this.escapeHtml(description)}">
                                ${this.escapeHtml(description)}
                            </span>
                        </div>
                        
                        <!-- 🟣 文件URL标签 -->
                        <div class="shiroki-media-label-url" data-url="${this.escapeHtml(item.url)}">
                            <span class="shiroki-media-label-title">文件URL</span>
                            <span class="shiroki-media-label-content" title="${this.escapeHtml(item.url)}">
                                ${shortUrl}
                            </span>
                        </div>
                    </div>
                    
                    <!-- ⚡ 操作按钮 -->
                    <div class="shiroki-media-actions">
                        <button class="shiroki-media-btn shiroki-media-btn-view" data-url="${this.escapeHtml(item.url)}">
                            👁️ 查看
                        </button>
                        <button class="shiroki-media-btn shiroki-media-btn-edit" data-link="${item.edit_link}">
                            ✏️ 编辑
                        </button>
                        <button class="shiroki-media-btn shiroki-media-btn-delete" data-id="${item.id}">
                            🗑️ 删除
                        </button>
                    </div>
                </div>
            `;
        },

        /**
         * 🏷️ 获取文件类型标签
         */
        getFileTypeLabel: function(type) {
            const labels = {
                'image': '🖼️ 图片',
                'video': '🎬 视频',
                'audio': '🎵 音频',
                'document': '📄 文档',
                'file': '📦 文件'
            };
            return labels[type] || '📦 文件';
        },

        /**
         * ✂️ 截断URL显示
         */
        truncateUrl: function(url, maxLength) {
            if (url.length <= maxLength) return url;
            return url.substring(0, maxLength) + '...';
        },

        /**
         * 🛡️ HTML转义
         */
        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * 🖼️ 打开WordPress原生媒体编辑弹窗
         */
        openMediaModal: function(id) {
            /* ◀️ 使用WordPress原生的媒体管理器打开编辑弹窗 */
            if (typeof wp !== 'undefined' && wp.media) {
                /* ◀️ 创建编辑附件的媒体管理器 */
                const frame = new wp.media.view.MediaFrame.EditAttachments({
                    controller: {
                        trigger: function() {}
                    },
                    library: wp.media.query({
                        post__in: [id]
                    }),
                    model: wp.media.attachment(id)
                });

                /* ◀️ 打开媒体管理器 */
                frame.open();
            } else {
                /* ◀️ 降级方案：跳转到编辑页面 */
                window.location.href = shirokiMediaConfig.adminUrl + 'post.php?post=' + id + '&action=edit';
            }
        },

        /**
         * 🔄 更新批量操作工具栏
         */
        updateBulkActions: function() {
            const $bulkActions = $('#shiroki-media-bulk-actions');
            const $sortWrapper = $('.shiroki-media-sort-wrapper');
            const count = this.state.selectedItems.size;

            if (count > 0) {
                $sortWrapper.hide();
                $bulkActions.show();
                $bulkActions.find('.shiroki-media-bulk-count-num').text(count);
            } else {
                $sortWrapper.show();
                $bulkActions.hide();
            }
        },

        /**
         * 🗑️ 批量删除
         */
        bulkDelete: function() {
            const ids = Array.from(this.state.selectedItems);
            if (ids.length === 0) return;

            if (!confirm('⚠️ 确定要删除选中的 ' + ids.length + ' 个媒体文件吗？此操作不可恢复。')) {
                return;
            }

            let deletedCount = 0;
            const total = ids.length;

            ids.forEach(id => {
                $.ajax({
                    url: shirokiMediaConfig.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'shiroki_delete_media',
                        nonce: shirokiMediaConfig.nonce,
                        id: id
                    },
                    success: (response) => {
                        if (response.success) {
                            deletedCount++;
                            $(`.shiroki-media-card[data-id="${id}"]`).fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                    },
                    complete: () => {
                        if (deletedCount === total) {
                            this.state.selectedItems.clear();
                            this.updateBulkActions();
                            if ($('.shiroki-media-card').length === 0) {
                                $('#shiroki-media-empty').show();
                                $('#shiroki-media-grid').hide();
                            }
                        }
                    }
                });
            });
        },

        /**
         * ❌ 取消选择
         */
        clearSelection: function() {
            this.state.selectedItems.clear();
            $('.shiroki-media-card').removeClass('selected');
            this.updateBulkActions();
        },

        /**
         * 📋 复制到剪贴板
         */
        copyToClipboard: function(text, $element) {
            if (navigator.clipboard && window.isSecureContext) {
                // ◀️ 使用现代Clipboard API
                navigator.clipboard.writeText(text).then(() => {
                    this.showCopySuccess($element);
                }).catch(() => {
                    this.fallbackCopy(text, $element);
                });
            } else {
                // ◀️ 降级方案
                this.fallbackCopy(text, $element);
            }
        },

        /**
         * 📋 降级复制方案
         */
        fallbackCopy: function(text, $element) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                this.showCopySuccess($element);
            } catch (err) {
                alert(shirokiMediaConfig.strings.copyError);
            }

            document.body.removeChild(textArea);
        },

        /**
         * ✅ 显示复制成功
         */
        showCopySuccess: function($element) {
            $element.addClass('copied');
            
            // ◀️ 临时改变内容
            const $content = $element.find('.shiroki-media-label-content');
            const originalText = $content.text();
            $content.text('✅ 已复制!');
            
            setTimeout(() => {
                $element.removeClass('copied');
                $content.text(originalText);
            }, 1500);
        },

        /**
         * 🗑️ 删除媒体项目
         */
        deleteMediaItem: function(id, $card) {
            if (!confirm('⚠️ 确定要删除这个媒体文件吗？此操作不可恢复。')) {
                return;
            }

            /* ◀️ 禁用卡片交互并显示删除中状态 */
            $card.css('opacity', '0.5').addClass('deleting');

            /* ◀️ 使用WordPress原生的删除attachment的AJAX action */
            $.ajax({
                url: shirokiMediaConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_delete_media',
                    nonce: shirokiMediaConfig.nonce,
                    id: id
                },
                dataType: 'json',
                success: (response) => {
                    if (response && response.success) {
                        /* ◀️ 动画移除卡片 */
                        $card.fadeOut(400, function() {
                            $(this).remove();

                            /* ◀️ 如果网格为空，显示空状态 */
                            if ($('.shiroki-media-card').length === 0) {
                                $('#shiroki-media-empty').show();
                                $('#shiroki-media-grid').hide();
                            }
                        });
                    } else {
                        const errorMsg = response && response.data ? response.data : '未知错误';
                        alert('❌ 删除失败：' + errorMsg);
                        $card.css('opacity', '1').removeClass('deleting');
                    }
                },
                error: (xhr, status, error) => {
                    alert('❌ 删除请求失败，请检查网络连接');
                    $card.css('opacity', '1').removeClass('deleting');
                }
            });
        },

        /**
         * ⏳ 显示加载状态
         */
        showLoading: function() {
            if (this.state.page === 1) {
                this.$grid.html('<div class="shiroki-media-loading">' + shirokiMediaConfig.strings.loading + '</div>');
            }
        },

        /**
         * ✅ 隐藏加载状态
         */
        hideLoading: function() {
            $('.shiroki-media-loading').remove();
        },

        /**
         * 📜 检查是否接近底部
         */
        isNearBottom: function() {
            const scrollTop = $(window).scrollTop();
            const windowHeight = $(window).height();
            const documentHeight = $(document).height();
            
            return scrollTop + windowHeight >= documentHeight - 200;
        },

        /**
         * ⏱️ 防抖函数
         */
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    /**
     * 🚀 DOM加载完成后初始化
     */
    $(document).ready(function() {
        // ◀️ 仅在媒体库页面初始化
        if ($('body').hasClass('upload-php')) {
            // ◀️ 延迟初始化，确保PHP注入的HTML已存在
            setTimeout(function() {
                ShirokiMediaLibrary.init();
            }, 100);
        }
    });

    /**
     * 🌐 暴露到全局（供其他脚本使用）
     */
    window.ShirokiMediaLibrary = ShirokiMediaLibrary;

})(jQuery);
