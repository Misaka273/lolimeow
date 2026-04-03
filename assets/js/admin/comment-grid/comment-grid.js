/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 💬 评论网格卡片式布局 JavaScript
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage Comment_Grid
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 🎯 评论网格管理器
     */
    var CommentGrid = {
        /* 📊 状态 */
        currentPage: 1,
        currentStatus: 'all',
        currentSearch: '',
        postId: 0,
        selectedComments: [],
        isLoading: false,
        hasMore: true,
        isLazyLoad: true, // ◀️ 启用懒加载模式
        perPage: 8, // ◀️ 每页加载数量

        /**
         * 🚀 初始化
         */
        init: function(postId) {
            this.postId = postId || 0;
            this.bindEvents();
            this.loadComments();
            this.createReplyModal();
            this.createToast();
            this.initLazyLoad();
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            var self = this;

            /* 📊 状态筛选 */
            $(document).on('click', '.shiroki-comment-status-btn', function() {
                var status = $(this).data('status');
                self.currentStatus = status;
                self.currentSearch = ''; /* ◀️ 切换状态时清空搜索 */
                $('#shiroki-comment-search').val('');

                $('.shiroki-comment-status-btn').removeClass('active');
                $(this).addClass('active');

                /* 🔄 重置并加载 */
                self.loadComments(false);
            });

            /* 🔍 搜索模式下加载所有匹配结果，不使用懒加载 */
            var searchTimeout;
            $(document).on('input', '#shiroki-comment-search', function() {
                clearTimeout(searchTimeout);
                var search = $(this).val();

                searchTimeout = setTimeout(function() {
                    self.currentSearch = search;
                    /* 🔍 搜索时重置并加载所有结果 */
                    self.loadComments(false);
                }, 300);
            });

            /* ✅ 选择评论 */
            $(document).on('click', '.shiroki-comment-select-circle', function(e) {
                e.stopPropagation();
                var card = $(this).closest('.shiroki-comment-card');
                var commentId = card.data('id');

                self.toggleSelection(commentId, card);
            });

            /* 🎴 卡片点击（选择） */
            $(document).on('click', '.shiroki-comment-card', function(e) {
                /* ◀️ 如果点击的是按钮或链接，不触发选择 */
                if ($(e.target).closest('a, button').length) {
                    return;
                }

                var card = $(this);
                var commentId = card.data('id');

                self.toggleSelection(commentId, card);
            });

            /* 📦 批量操作 */
            $(document).on('click', '.shiroki-comment-bulk-btn', function() {
                var action = $(this).data('action');
                self.handleBulkAction(action);
            });

            /* ⚡ 单个操作按钮 */
            $(document).on('click', '.shiroki-comment-btn', function(e) {
                e.stopPropagation();
                var btn = $(this);
                var action = btn.data('action');
                var commentId = btn.closest('.shiroki-comment-card').data('id');

                self.handleSingleAction(action, commentId, btn);
            });
        },

        /**
         * 📡 加载评论列表
         * @param {boolean} append - 是否追加模式（懒加载）
         */
        loadComments: function(append) {
            var self = this;

            if (self.isLoading) return;
            self.isLoading = true;

            /* 📝 非追加模式时重置状态 */
            if (!append) {
                self.currentPage = 1;
                self.hasMore = true;
                $('#shiroki-comment-grid').empty();
                $('#shiroki-comment-grid').hide();
                $('#shiroki-comment-empty').hide();
            }

            /* ⏳ 显示加载状态 */
            if (!append) {
                $('#shiroki-comment-loading').show();
            } else {
                $('#shiroki-comment-load-more').show();
            }

            $.ajax({
                url: shirokiCommentConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_get_comments',
                    nonce: shirokiCommentConfig.nonce,
                    page: self.currentPage,
                    per_page: self.perPage,
                    status: self.currentStatus,
                    search: self.currentSearch,
                    post_id: self.postId
                },
                success: function(response) {
                    if (response.success) {
                        self.hasMore = response.data.has_more;

                        if (append) {
                            /* 📦 追加模式：添加新评论到现有列表 */
                            self.appendComments(response.data.comments);
                        } else {
                            /* 🔄 重置模式：渲染新列表 */
                            self.renderComments(response.data.comments);
                            if (response.data.comments.length === 0) {
                                $('#shiroki-comment-empty').show();
                            } else {
                                $('#shiroki-comment-grid').show();
                            }
                        }
                    } else {
                        self.showToast(response.data.message || '加载失败', 'error');
                        if (!append) {
                            $('#shiroki-comment-empty').show();
                        }
                    }
                },
                error: function() {
                    self.showToast('网络错误，请稍后重试', 'error');
                    if (!append) {
                        $('#shiroki-comment-empty').show();
                    }
                },
                complete: function() {
                    self.isLoading = false;
                    $('#shiroki-comment-loading').hide();
                    $('#shiroki-comment-load-more').hide();
                }
            });
        },

        /**
         * 📦 追加评论到列表（懒加载）
         */
        appendComments: function(comments) {
            var self = this;

            /* 🎨 明亮浅色勋章颜色数组 */
            var badgeColors = [
                'red', 'orange', 'yellow', 'green', 'cyan',
                'blue', 'purple', 'pink', 'gray', 'brown',
                'rose', 'amber', 'emerald', 'indigo'
            ];

            /* 📊 获取当前已有评论数量，用于颜色分配 */
            var existingCount = $('#shiroki-comment-grid .shiroki-comment-card').length;

            $.each(comments, function(index, comment) {
                /* 🎨 根据评论ID分配颜色，确保每个卡片颜色不同且固定 */
                var colorIndex = (comment.id + existingCount + index) % badgeColors.length;
                var badgeColor = badgeColors[colorIndex];

                var cardHtml = self.createCommentCard(comment, badgeColor);
                $('#shiroki-comment-grid').append(cardHtml);
            });
        },

        /**
         * 🔄 初始化懒加载
         */
        initLazyLoad: function() {
            var self = this;

            /* 📦 添加加载更多指示器 */
            var loadMoreHtml = `
                <div class="shiroki-comment-load-more" id="shiroki-comment-load-more" style="display: none;">
                    <div class="shiroki-comment-load-more-spinner"></div>
                    <span>⏳ 加载更多...</span>
                </div>
            `;
            $('#shiroki-comment-grid').after(loadMoreHtml);

            /* 📜 滚动监听 */
            var scrollTimeout;
            $(window).on('scroll.shirokiLazyLoad', function() {
                if (scrollTimeout) {
                    clearTimeout(scrollTimeout);
                }

                scrollTimeout = setTimeout(function() {
                    /* 🔍 检查是否滚动到底部附近 */
                    var scrollTop = $(window).scrollTop();
                    var windowHeight = $(window).height();
                    var documentHeight = $(document).height();

                    /* ◀️ 距离底部 200px 时触发加载 */
                    if (scrollTop + windowHeight >= documentHeight - 200) {
                        self.loadMoreComments();
                    }
                }, 100);
            });
        },

        /**
         * 📥 加载更多评论（懒加载触发）
         */
        loadMoreComments: function() {
            /* 🔍 检查是否可以加载更多 */
            if (!this.hasMore || this.isLoading || this.currentSearch) {
                /* ◀️ 搜索模式下不启用懒加载，直接显示所有结果 */
                return;
            }

            this.currentPage++;
            this.loadComments(true);
        },

        /**
     * 🎨 渲染评论列表
     */
    renderComments: function(comments) {
        var self = this;
        var html = '';

        /* 🎨 明亮浅色勋章颜色数组 */
        var badgeColors = [
            'red', 'orange', 'yellow', 'green', 'cyan',
            'blue', 'purple', 'pink', 'gray', 'brown',
            'rose', 'amber', 'emerald', 'indigo'
        ];

        $.each(comments, function(index, comment) {
            /* 🎨 根据评论ID分配颜色，确保每个卡片颜色不同且固定 */
            var colorIndex = (comment.id + index) % badgeColors.length;
            var badgeColor = badgeColors[colorIndex];

            html += self.createCommentCard(comment, badgeColor);
        });

        $('#shiroki-comment-grid').html(html);
    },

        /**
         * 🃏 创建评论卡片
         */
        createCommentCard: function(comment, badgeColor) {
            var statusTag = '';
            var statusClass = '';

            switch (comment.status) {
                case '0':
                case 'hold':
                    statusTag = '🟠 待审核';
                    statusClass = 'pending';
                    break;
                case '1':
                case 'approve':
                    statusTag = '🟢 已批准';
                    statusClass = 'approved';
                    break;
                case 'spam':
                    statusTag = '🚫 垃圾';
                    statusClass = 'spam';
                    break;
                case 'trash':
                    statusTag = '🗑️ 回收站';
                    statusClass = 'trash';
                    break;
            }

            var typeTag = comment.type_label ? '<span class="shiroki-comment-type-tag">' + comment.type_label + '</span>' : '';

            var replyBadge = comment.reply_count > 0 ? '<span class="shiroki-comment-replies">💬 ' + comment.reply_count + '</span>' : '';

            var actionButtons = this.getActionButtons(comment.status);

            /* 🎨 勋章颜色类名 */
            var badgeClass = badgeColor ? 'shiroki-comment-post-badge-' + badgeColor : '';

            return `
                <div class="shiroki-comment-card" data-id="${comment.id}">
                    <div class="shiroki-comment-select-circle">
                        <div class="shiroki-comment-select-inner"></div>
                    </div>

                    <div class="shiroki-comment-header">
                        <div class="shiroki-comment-avatar">
                            ${comment.avatar}
                        </div>
                        <div class="shiroki-comment-author-info">
                            <div class="shiroki-comment-author-name">${this.escapeHtml(comment.author_name)}</div>
                            <div class="shiroki-comment-author-email">${this.escapeHtml(comment.author_email)}</div>
                        </div>
                    </div>

                    <div class="shiroki-comment-content">
                        ${typeTag}
                        <p>${comment.content_excerpt}</p>
                    </div>

                    <div class="shiroki-comment-post-info">
                        <a href="${comment.post_edit_link}" class="shiroki-comment-post-badge ${badgeClass}" target="_blank" title="编辑文章">
                            📄 ${this.escapeHtml(comment.post_title)}
                        </a>
                    </div>

                    <div class="shiroki-comment-meta">
                        <span class="shiroki-comment-date">${comment.time_ago}</span>
                        <div>
                            ${replyBadge}
                            <span class="shiroki-comment-status-tag ${statusClass}">${statusTag}</span>
                        </div>
                    </div>

                    <div class="shiroki-comment-actions">
                        ${actionButtons}
                    </div>
                </div>
            `;
        },

        /**
         * 🔘 获取操作按钮
         */
        getActionButtons: function(status) {
            var buttons = '';

            /* 👁️ 查看按钮 */
            buttons += '<button class="shiroki-comment-btn shiroki-comment-btn-view" data-action="view"><span class="shiroki-comment-btn-icon">👁️</span><span class="shiroki-comment-btn-text">查看</span></button>';

            /* ✏️ 编辑按钮 */
            buttons += '<button class="shiroki-comment-btn shiroki-comment-btn-edit" data-action="edit"><span class="shiroki-comment-btn-icon">✏️</span><span class="shiroki-comment-btn-text">编辑</span></button>';

            if (status === 'trash') {
                /* ♻️ 回收站状态：还原和删除 */
                buttons += '<button class="shiroki-comment-btn shiroki-comment-btn-restore" data-action="untrash"><span class="shiroki-comment-btn-icon">♻️</span><span class="shiroki-comment-btn-text">还原</span></button>';
                buttons += '<button class="shiroki-comment-btn shiroki-comment-btn-delete" data-action="delete"><span class="shiroki-comment-btn-icon">🗑️</span><span class="shiroki-comment-btn-text">删除</span></button>';
            } else if (status === 'spam') {
                /* 🚫 垃圾评论状态：恢复和删除 */
                buttons += '<button class="shiroki-comment-btn shiroki-comment-btn-restore" data-action="unspam"><span class="shiroki-comment-btn-icon">♻️</span><span class="shiroki-comment-btn-text">恢复</span></button>';
                buttons += '<button class="shiroki-comment-btn shiroki-comment-btn-delete" data-action="delete"><span class="shiroki-comment-btn-icon">🗑️</span><span class="shiroki-comment-btn-text">删除</span></button>';
            } else {
                /* 💬 回复按钮 */
                buttons += '<button class="shiroki-comment-btn shiroki-comment-btn-reply" data-action="reply"><span class="shiroki-comment-btn-icon">💬</span><span class="shiroki-comment-btn-text">回复</span></button>';

                /* ✅/❌ 批准/驳回按钮 */
                if (status === '0' || status === 'hold') {
                    buttons += '<button class="shiroki-comment-btn shiroki-comment-btn-approve" data-action="approve"><span class="shiroki-comment-btn-icon">✅</span><span class="shiroki-comment-btn-text">批准</span></button>';
                } else {
                    buttons += '<button class="shiroki-comment-btn shiroki-comment-btn-unapprove" data-action="unapprove"><span class="shiroki-comment-btn-icon">❌</span><span class="shiroki-comment-btn-text">驳回</span></button>';
                }

                /* 🚫 垃圾按钮 */
                buttons += '<button class="shiroki-comment-btn shiroki-comment-btn-spam" data-action="spam"><span class="shiroki-comment-btn-icon">🚫</span><span class="shiroki-comment-btn-text">垃圾</span></button>';

                /* 🗑️ 回收站按钮 */
                buttons += '<button class="shiroki-comment-btn shiroki-comment-btn-trash" data-action="trash"><span class="shiroki-comment-btn-icon">🗑️</span><span class="shiroki-comment-btn-text">回收站</span></button>';
            }

            return buttons;
        },

        /**
         * ✅ 切换选择状态
         */
        toggleSelection: function(commentId, card) {
            var index = this.selectedComments.indexOf(commentId);

            if (index === -1) {
                this.selectedComments.push(commentId);
                card.addClass('selected');
            } else {
                this.selectedComments.splice(index, 1);
                card.removeClass('selected');
            }

            this.updateBulkActions();
        },

        /**
         * 📦 更新批量操作显示
         */
        updateBulkActions: function() {
            var count = this.selectedComments.length;

            if (count > 0) {
                $('#shiroki-comment-bulk-actions').show();
                $('.shiroki-comment-filter-wrapper').hide();
            } else {
                $('#shiroki-comment-bulk-actions').hide();
                $('.shiroki-comment-filter-wrapper').show();
            }

            $('.shiroki-comment-bulk-count-num').text(count);
        },

        /**
         * 📦 处理批量操作
         */
        handleBulkAction: function(action) {
            var self = this;

            if (action === 'cancel') {
                this.selectedComments = [];
                $('.shiroki-comment-card').removeClass('selected');
                this.updateBulkActions();
                return;
            }

            if (this.selectedComments.length === 0) {
                this.showToast('请先选择评论', 'error');
                return;
            }

            /* 🎨 显示自定义确认弹窗 */
            this.showBulkConfirmModal(action, this.selectedComments.length);
        },

        /**
         * 🎨 显示批量操作确认弹窗
         */
        showBulkConfirmModal: function(action, count) {
            var self = this;

            /* 📝 根据操作类型设置弹窗内容 */
            var config = {
                'delete': {
                    icon: '🗑️',
                    title: '确认永久删除',
                    message: '确定要永久删除选中的 <span class="shiroki-comment-bulk-confirm-count">' + count + '</span> 条评论吗？',
                    hint: '⚠️ 此操作不可恢复，请谨慎操作！',
                    confirmText: '确认删除',
                    confirmClass: 'shiroki-comment-bulk-confirm-danger',
                    danger: true
                },
                'trash': {
                    icon: '🗑️',
                    title: '确认移至回收站',
                    message: '确定要将选中的 <span class="shiroki-comment-bulk-confirm-count">' + count + '</span> 条评论移至回收站吗？',
                    hint: '💡 移至回收站的评论可以随时恢复',
                    confirmText: '移至回收站',
                    confirmClass: 'shiroki-comment-bulk-confirm-warning',
                    danger: false
                },
                'spam': {
                    icon: '🚫',
                    title: '确认标记为垃圾',
                    message: '确定要将选中的 <span class="shiroki-comment-bulk-confirm-count">' + count + '</span> 条评论标记为垃圾吗？',
                    hint: '💡 标记为垃圾的评论将被过滤',
                    confirmText: '标记垃圾',
                    confirmClass: 'shiroki-comment-bulk-confirm-purple',
                    danger: false
                },
                'unspam': {
                    icon: '♻️',
                    title: '确认恢复评论',
                    message: '确定要恢复选中的 <span class="shiroki-comment-bulk-confirm-count">' + count + '</span> 条垃圾评论吗？',
                    hint: '💡 恢复后的评论将回到正常状态',
                    confirmText: '确认恢复',
                    confirmClass: 'shiroki-comment-bulk-confirm-success',
                    danger: false
                },
                'untrash': {
                    icon: '♻️',
                    title: '确认还原评论',
                    message: '确定要还原选中的 <span class="shiroki-comment-bulk-confirm-count">' + count + '</span> 条评论吗？',
                    hint: '💡 还原后的评论将回到正常状态',
                    confirmText: '确认还原',
                    confirmClass: 'shiroki-comment-bulk-confirm-success',
                    danger: false
                }
            };

            var cfg = config[action] || config['delete'];

            /* 📦 创建弹窗HTML */
            var modalHtml = `
                <div class="shiroki-comment-bulk-confirm-modal" id="shiroki-comment-bulk-confirm-modal">
                    <div class="shiroki-comment-bulk-confirm-backdrop"></div>
                    <div class="shiroki-comment-bulk-confirm-content">
                        <div class="shiroki-comment-bulk-confirm-header">
                            <span class="shiroki-comment-bulk-confirm-title">${cfg.title}</span>
                            <button class="shiroki-comment-bulk-confirm-close">&times;</button>
                        </div>
                        <div class="shiroki-comment-bulk-confirm-body">
                            <div class="shiroki-comment-bulk-confirm-icon">${cfg.icon}</div>
                            <p class="shiroki-comment-bulk-confirm-message">${cfg.message}</p>
                            <p class="shiroki-comment-bulk-confirm-hint">${cfg.hint}</p>
                        </div>
                        <div class="shiroki-comment-bulk-confirm-footer">
                            <button class="shiroki-comment-bulk-confirm-btn shiroki-comment-bulk-confirm-cancel">取消</button>
                            <button class="shiroki-comment-bulk-confirm-btn ${cfg.confirmClass}" data-action="${action}">${cfg.confirmText}</button>
                        </div>
                    </div>
                </div>
            `;

            /* 🗑️ 移除已存在的弹窗 */
            $('#shiroki-comment-bulk-confirm-modal').remove();

            /* 📦 添加弹窗到页面 */
            $('body').append(modalHtml);

            /* 🎬 显示弹窗 */
            setTimeout(function() {
                $('#shiroki-comment-bulk-confirm-modal').addClass('active');
            }, 10);

            /* 🔗 绑定关闭事件 */
            $('#shiroki-comment-bulk-confirm-modal .shiroki-comment-bulk-confirm-close, #shiroki-comment-bulk-confirm-modal .shiroki-comment-bulk-confirm-cancel, #shiroki-comment-bulk-confirm-modal .shiroki-comment-bulk-confirm-backdrop').on('click', function() {
                $('#shiroki-comment-bulk-confirm-modal').removeClass('active');
                setTimeout(function() {
                    $('#shiroki-comment-bulk-confirm-modal').remove();
                }, 300);
            });

            /* ✅ 绑定确认事件 */
            $('#shiroki-comment-bulk-confirm-modal .shiroki-comment-bulk-confirm-btn[data-action]').on('click', function() {
                var actionType = $(this).data('action');
                $('#shiroki-comment-bulk-confirm-modal').removeClass('active');
                setTimeout(function() {
                    $('#shiroki-comment-bulk-confirm-modal').remove();
                    self.executeBulkAction(actionType);
                }, 300);
            });
        },

        /**
         * 📡 执行批量操作
         */
        executeBulkAction: function(action) {
            var self = this;

            $.ajax({
                url: shirokiCommentConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_bulk_action_comments',
                    nonce: shirokiCommentConfig.nonce,
                    comment_ids: this.selectedComments.join(','),
                    bulk_action: action
                },
                success: function(response) {
                    if (response.success) {
                        self.showToast(response.data.message, 'success');
                        self.selectedComments = [];
                        $('.shiroki-comment-card').removeClass('selected');
                        self.updateBulkActions();
                        self.loadComments();
                    } else {
                        self.showToast(response.data.message || '操作失败', 'error');
                    }
                },
                error: function() {
                    self.showToast('网络错误，请稍后重试', 'error');
                }
            });
        },

        /**
         * ⚡ 处理单个操作
         */
        handleSingleAction: function(action, commentId, btn) {
            var self = this;

            switch (action) {
                case 'view':
                    var viewLink = btn.closest('.shiroki-comment-card').find('.shiroki-comment-btn-view').data('view-link');
                    if (viewLink) {
                        window.open(viewLink, '_blank');
                    }
                    break;

                case 'edit':
                    window.open(shirokiCommentConfig.adminUrl + 'comment.php?action=editcomment&c=' + commentId, '_blank');
                    break;

                case 'reply':
                    this.openReplyModal(commentId);
                    break;

                case 'delete':
                    if (!confirm('确定要永久删除这条评论吗？此操作不可恢复！')) {
                        return;
                    }
                    this.executeAction(action, commentId);
                    break;

                default:
                    this.executeAction(action, commentId);
                    break;
            }
        },

        /**
         * 📡 执行AJAX操作
         */
        executeAction: function(action, commentId) {
            var self = this;
            var actionMap = {
                'approve': 'shiroki_approve_comment',
                'unapprove': 'shiroki_unapprove_comment',
                'spam': 'shiroki_spam_comment',
                'unspam': 'shiroki_unspam_comment',
                'trash': 'shiroki_trash_comment',
                'untrash': 'shiroki_untrash_comment',
                'delete': 'shiroki_delete_comment'
            };

            var ajaxAction = actionMap[action];
            if (!ajaxAction) return;

            $.ajax({
                url: shirokiCommentConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: ajaxAction,
                    nonce: shirokiCommentConfig.nonce,
                    comment_id: commentId
                },
                success: function(response) {
                    if (response.success) {
                        self.showToast(response.data.message, 'success');
                        self.loadComments();
                    } else {
                        self.showToast(response.data.message || '操作失败', 'error');
                    }
                },
                error: function() {
                    self.showToast('网络错误，请稍后重试', 'error');
                }
            });
        },

        /**
         * 💬 创建回复弹窗
         */
        createReplyModal: function() {
            var modal = `
                <div class="shiroki-comment-reply-modal" id="shiroki-comment-reply-modal">
                    <div class="shiroki-comment-reply-content">
                        <div class="shiroki-comment-reply-header">
                            <span class="shiroki-comment-reply-title">💬 回复评论</span>
                            <button class="shiroki-comment-reply-close">&times;</button>
                        </div>
                        <div class="shiroki-comment-reply-body">
                            <div class="shiroki-comment-reply-original">
                                <span class="shiroki-comment-reply-original-label">原文内容</span>
                                <div class="shiroki-comment-reply-original-text"></div>
                            </div>
                            <textarea class="shiroki-comment-reply-textarea" placeholder="请输入回复内容..."></textarea>
                        </div>
                        <div class="shiroki-comment-reply-footer">
                            <button class="shiroki-comment-reply-cancel">取消</button>
                            <button class="shiroki-comment-reply-submit">发送回复</button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modal);

            var self = this;
            var currentReplyId = 0;

            /* 🔒 关闭弹窗 */
            $(document).on('click', '.shiroki-comment-reply-close, .shiroki-comment-reply-cancel', function() {
                $('#shiroki-comment-reply-modal').removeClass('active');
                currentReplyId = 0;
            });

            /* 📤 发送回复 */
            $(document).on('click', '.shiroki-comment-reply-submit', function() {
                var content = $('.shiroki-comment-reply-textarea').val().trim();

                if (!content) {
                    self.showToast('请输入回复内容', 'error');
                    return;
                }

                $.ajax({
                    url: shirokiCommentConfig.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'shiroki_reply_comment',
                        nonce: shirokiCommentConfig.nonce,
                        comment_id: currentReplyId,
                        content: content
                    },
                    success: function(response) {
                        if (response.success) {
                            self.showToast('回复成功', 'success');
                            $('#shiroki-comment-reply-modal').removeClass('active');
                            $('.shiroki-comment-reply-textarea').val('');
                            self.loadComments();
                        } else {
                            self.showToast(response.data.message || '回复失败', 'error');
                        }
                    },
                    error: function() {
                        self.showToast('网络错误，请稍后重试', 'error');
                    }
                });
            });

            this.openReplyModal = function(commentId) {
                currentReplyId = commentId;

                /* 📋 获取评论内容 */
                $.ajax({
                    url: shirokiCommentConfig.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'shiroki_get_comment_content',
                        nonce: shirokiCommentConfig.nonce,
                        comment_id: commentId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.shiroki-comment-reply-original-text').text(response.data.content);
                            $('#shiroki-comment-reply-modal').addClass('active');
                        }
                    }
                });
            };
        },

        /**
         * 🔔 创建 Toast 提示
         */
        createToast: function() {
            $('body').append('<div class="shiroki-comment-toast" id="shiroki-comment-toast"></div>');

            this.showToast = function(message, type) {
                var toast = $('#shiroki-comment-toast');
                toast.text(message).removeClass('success error').addClass(type).addClass('show');

                setTimeout(function() {
                    toast.removeClass('show');
                }, 3000);
            };
        },

        /**
         * 🛡️ HTML转义
         */
        escapeHtml: function(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    /**
     * 🚀 初始化
     */
    $(document).on('shiroki-comment-grid-ready', function(event, postId) {
        CommentGrid.init(postId);
    });

})(jQuery);
