/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 👥 用户列表网格卡片式布局 JavaScript
 * 🎨 拟态拟物玻璃质感设计
 */

(function($) {
    'use strict';

    /**
     * 🎯 用户网格管理器
     */
    var UserGridManager = {
        /* 📊 状态 */
        currentPage: 1,
        totalPages: 1,
        currentRole: 'all',
        searchQuery: '',
        selectedUsers: [],
        isLoading: false,

        /**
         * 🚀 初始化
         */
        init: function() {
            this.bindEvents();
            this.loadUsers();
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            var self = this;

            /* 🎭 角色筛选 */
            $(document).on('click', '.shiroki-user-role-btn', function() {
                var role = $(this).data('role');
                self.filterByRole(role);
            });

            /* 🔍 搜索 */
            var searchTimeout;
            $(document).on('input', '#shiroki-user-search', function() {
                clearTimeout(searchTimeout);
                var query = $(this).val();
                searchTimeout = setTimeout(function() {
                    self.searchUsers(query);
                }, 300);
            });

            /* ✅ 选择用户 */
            $(document).on('click', '.shiroki-user-select-circle', function(e) {
                e.stopPropagation();
                var userId = $(this).closest('.shiroki-user-card').data('user-id');
                self.toggleSelection(userId);
            });

            $(document).on('click', '.shiroki-user-card', function(e) {
                /* 🔗 如果点击的是按钮或链接，不触发选择 */
                if ($(e.target).closest('a, button').length) {
                    return;
                }
                var userId = $(this).data('user-id');
                self.toggleSelection(userId);
            });

            /* 📦 批量操作 */
            $(document).on('click', '.shiroki-user-bulk-btn', function() {
                var action = $(this).data('action');
                self.handleBulkAction(action);
            });

            /* 📄 分页 */
            $(document).on('click', '.shiroki-user-page-btn', function() {
                var page = $(this).data('page');
                if (page && !$(this).hasClass('active') && !$(this).prop('disabled')) {
                    self.goToPage(page);
                }
            });

            /* 🗑️ 单个删除 */
            $(document).on('click', '.shiroki-user-btn-delete', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var userId = $(this).closest('.shiroki-user-card').data('user-id');
                var userName = $(this).closest('.shiroki-user-card').find('.shiroki-user-name').text();
                self.deleteUser(userId, userName);
            });
        },

        /**
         * 📡 加载用户列表
         */
        loadUsers: function() {
            if (this.isLoading) return;

            this.isLoading = true;
            this.showLoading();

            $.ajax({
                url: shirokiUserConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_get_users',
                    nonce: shirokiUserConfig.nonce,
                    page: this.currentPage,
                    per_page: 12,
                    role: this.currentRole,
                    search: this.searchQuery
                },
                success: function(response) {
                    if (response.success) {
                        this.renderUsers(response.data.users);
                        this.renderPagination(response.data.current_page, response.data.total_pages);
                        this.totalPages = response.data.total_pages;
                    } else {
                        this.showError(response.data.message || '加载失败');
                    }
                }.bind(this),
                error: function() {
                    this.showError('网络错误，请稍后重试');
                }.bind(this),
                complete: function() {
                    this.isLoading = false;
                    this.hideLoading();
                }.bind(this)
            });
        },

        /**
         * 🎨 渲染用户列表
         */
        renderUsers: function(users) {
            var $grid = $('#shiroki-user-grid');
            var $empty = $('#shiroki-user-empty');

            if (!users || users.length === 0) {
                $grid.empty();
                $empty.show();
                return;
            }

            $empty.hide();

            var html = users.map(function(user) {
                var isSelected = this.selectedUsers.indexOf(user.id) !== -1;
                return `
                    <div class="shiroki-user-card ${isSelected ? 'selected' : ''}" data-user-id="${user.id}">
                        <!-- 🔘 选择圆圈 -->
                        <div class="shiroki-user-select-circle">
                            <div class="shiroki-user-select-inner"></div>
                        </div>

                        <!-- 👤 用户头部 -->
                        <div class="shiroki-user-header">
                            <div class="shiroki-user-avatar">
                                ${user.avatar}
                            </div>
                            <div class="shiroki-user-info">
                                <div class="shiroki-user-name">${this.escapeHtml(user.display_name)}</div>
                                <div class="shiroki-user-login">@${this.escapeHtml(user.login)}</div>
                                <div class="shiroki-user-id">ID: ${user.custom_uid}</div>
                            </div>
                        </div>

                        <!-- 📧 邮箱 -->
                        <div class="shiroki-user-email-section">
                            <div class="shiroki-user-email">
                                📧 ${this.escapeHtml(user.email)}
                            </div>
                        </div>

                        <!-- 🎭 角色 -->
                        <div class="shiroki-user-role-section">
                            <span class="shiroki-user-role-badge shiroki-user-role-${user.role}">
                                ${user.role_name}
                            </span>
                        </div>

                        <!-- 📊 元信息 -->
                        <div class="shiroki-user-meta">
                            <span class="shiroki-user-registered">
                                📅 ${user.registered}
                            </span>
                            <span class="shiroki-user-posts">
                                📝 ${user.post_count} 篇文章
                            </span>
                        </div>

                        <!-- ⚡ 操作按钮 -->
                        <div class="shiroki-user-actions">
                            <a href="${user.edit_link}" target="_blank" class="shiroki-user-btn shiroki-user-btn-edit">
                                <span class="shiroki-user-btn-icon">✏️</span>
                                <span class="shiroki-user-btn-text">编辑</span>
                            </a>
                            <a href="${user.view_link}" target="_blank" class="shiroki-user-btn shiroki-user-btn-view">
                                <span class="shiroki-user-btn-icon">👁️</span>
                                <span class="shiroki-user-btn-text">查看</span>
                            </a>
                            <button type="button" class="shiroki-user-btn shiroki-user-btn-delete">
                                <span class="shiroki-user-btn-icon">🗑️</span>
                                <span class="shiroki-user-btn-text">删除</span>
                            </button>
                        </div>
                    </div>
                `;
            }.bind(this)).join('');

            if (this.currentPage === 1) {
                $grid.html(html);
            } else {
                $grid.append(html);
            }
        },

        /**
         * 📄 渲染分页
         */
        renderPagination: function(currentPage, totalPages) {
            var $pagination = $('#shiroki-user-pagination');

            if (totalPages <= 1) {
                $pagination.empty();
                return;
            }

            var html = '';

            /* ⬅️ 上一页 */
            html += `<button class="shiroki-user-page-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>‹</button>`;

            /* 🔢 页码 */
            var start = Math.max(1, currentPage - 2);
            var end = Math.min(totalPages, currentPage + 2);

            if (start > 1) {
                html += `<button class="shiroki-user-page-btn" data-page="1">1</button>`;
                if (start > 2) {
                    html += `<button class="shiroki-user-page-btn" disabled>...</button>`;
                }
            }

            for (var i = start; i <= end; i++) {
                html += `<button class="shiroki-user-page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
            }

            if (end < totalPages) {
                if (end < totalPages - 1) {
                    html += `<button class="shiroki-user-page-btn" disabled>...</button>`;
                }
                html += `<button class="shiroki-user-page-btn" data-page="${totalPages}">${totalPages}</button>`;
            }

            /* ➡️ 下一页 */
            html += `<button class="shiroki-user-page-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>›</button>`;

            $pagination.html(html);
        },

        /**
         * 🎭 按角色筛选
         */
        filterByRole: function(role) {
            this.currentRole = role;
            this.currentPage = 1;
            this.selectedUsers = [];
            this.updateBulkActions();

            /* 🎨 更新按钮状态 */
            $('.shiroki-user-role-btn').removeClass('active');
            $('.shiroki-user-role-btn[data-role="' + role + '"]').addClass('active');

            this.loadUsers();
        },

        /**
         * 🔍 搜索用户
         */
        searchUsers: function(query) {
            this.searchQuery = query;
            this.currentPage = 1;
            this.selectedUsers = [];
            this.updateBulkActions();
            this.loadUsers();
        },

        /**
         * 📄 跳转到指定页
         */
        goToPage: function(page) {
            this.currentPage = page;
            this.loadUsers();
            /* ⬆️ 滚动到顶部 */
            $('html, body').animate({
                scrollTop: $('.shiroki-user-wrapper').offset().top - 50
            }, 300);
        },

        /**
         * ✅ 切换选择状态
         */
        toggleSelection: function(userId) {
            var index = this.selectedUsers.indexOf(userId);
            var $card = $('.shiroki-user-card[data-user-id="' + userId + '"]');

            if (index === -1) {
                this.selectedUsers.push(userId);
                $card.addClass('selected');
            } else {
                this.selectedUsers.splice(index, 1);
                $card.removeClass('selected');
            }

            this.updateBulkActions();
        },

        /**
         * 📦 更新批量操作栏
         */
        updateBulkActions: function() {
            var $bulkActions = $('#shiroki-user-bulk-actions');
            var $roleFilter = $('.shiroki-user-filter-wrapper');
            var $searchBox = $('.shiroki-user-search-wrapper');
            var count = this.selectedUsers.length;

            if (count > 0) {
                $bulkActions.show();
                $('.shiroki-user-bulk-count-num').text(count);
                /* 🙈 多选模式下隐藏角色筛选器和搜索框 */
                $roleFilter.hide();
                $searchBox.hide();
            } else {
                $bulkActions.hide();
                /* 👁️ 取消选择后显示角色筛选器和搜索框 */
                $roleFilter.show();
                $searchBox.show();
            }
        },

        /**
         * 📦 处理批量操作
         */
        handleBulkAction: function(action) {
            if (action === 'cancel') {
                this.selectedUsers = [];
                $('.shiroki-user-card').removeClass('selected');
                this.updateBulkActions();
                return;
            }

            if (this.selectedUsers.length === 0) {
                alert('请先选择用户');
                return;
            }

            /* 🎭 更改角色 - 打开Modal窗口 */
            if (action === 'change_role') {
                this.openRoleModal();
                return;
            }

            var confirmMessage = '';
            switch (action) {
                case 'delete':
                    confirmMessage = '确定要删除选中的 ' + this.selectedUsers.length + ' 个用户吗？\n\n此操作不可恢复！';
                    break;
                default:
                    return;
            }

            if (!confirm(confirmMessage)) {
                return;
            }

            this.executeBulkAction(action);
        },

        /**
         * 📡 执行批量操作
         */
        executeBulkAction: function(action) {
            $.ajax({
                url: shirokiUserConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_bulk_action_users',
                    nonce: shirokiUserConfig.nonce,
                    user_ids: this.selectedUsers.join(','),
                    bulk_action: action
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        this.selectedUsers = [];
                        this.updateBulkActions();
                        this.loadUsers();
                    } else {
                        alert(response.data.message || '操作失败');
                    }
                }.bind(this),
                error: function() {
                    alert('网络错误，请稍后重试');
                }
            });
        },

        /**
         * 🪟 打开更改角色Modal窗口
         */
        openRoleModal: function() {
            var self = this;

            /* 🔄 重置选中状态 */
            $('.shiroki-user-role-option-btn').removeClass('selected');
            this.selectedRole = null;

            /* 🪟 显示Modal */
            $('#shiroki-user-role-modal').fadeIn(200);

            /* 🔗 绑定Modal事件（只绑定一次） */
            if (!this.roleModalBound) {
                this.bindRoleModalEvents();
                this.roleModalBound = true;
            }
        },

        /**
         * 🔗 绑定Modal事件
         */
        bindRoleModalEvents: function() {
            var self = this;

            /* ❌ 关闭Modal */
            $('#shiroki-user-role-modal-close, #shiroki-user-role-cancel').on('click', function() {
                self.closeRoleModal();
            });

            /* 🌫️ 点击背景关闭 */
            $('.shiroki-user-role-modal-backdrop').on('click', function() {
                self.closeRoleModal();
            });

            /* 🎭 选择角色 */
            $(document).on('click', '.shiroki-user-role-option-btn', function() {
                $('.shiroki-user-role-option-btn').removeClass('selected');
                $(this).addClass('selected');
                self.selectedRole = $(this).data('role');
            });

            /* ✅ 确认更改 */
            $('#shiroki-user-role-confirm').on('click', function() {
                if (!self.selectedRole) {
                    alert('请先选择一个角色');
                    return;
                }
                self.confirmChangeRole();
            });

            /* ⌨️ ESC键关闭 */
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#shiroki-user-role-modal').is(':visible')) {
                    self.closeRoleModal();
                }
            });
        },

        /**
         * ❌ 关闭Modal窗口
         */
        closeRoleModal: function() {
            $('#shiroki-user-role-modal').fadeOut(200);
            this.selectedRole = null;
            $('.shiroki-user-role-option-btn').removeClass('selected');
        },

        /**
         * ✅ 确认更改角色
         */
        confirmChangeRole: function() {
            var self = this;
            var roleNames = {
                'administrator': '管理员',
                'editor': '编辑',
                'author': '作者',
                'contributor': '贡献者',
                'subscriber': '订阅者'
            };
            var roleName = roleNames[this.selectedRole] || this.selectedRole;

            /* ⏳ 禁用按钮 */
            $('#shiroki-user-role-confirm').prop('disabled', true).text('⏳ 处理中...');

            $.ajax({
                url: shirokiUserConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_change_user_role',
                    nonce: shirokiUserConfig.nonce,
                    user_ids: this.selectedUsers.join(','),
                    new_role: this.selectedRole
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        self.selectedUsers = [];
                        self.updateBulkActions();
                        self.closeRoleModal();
                        self.loadUsers();
                    } else {
                        alert(response.data.message || '更改角色失败');
                    }
                },
                error: function() {
                    alert('网络错误，请稍后重试');
                },
                complete: function() {
                    /* 🔄 恢复按钮 */
                    $('#shiroki-user-role-confirm').prop('disabled', false).text('✅ 确认更改');
                }
            });
        },

        /**
         * 🗑️ 删除单个用户
         */
        deleteUser: function(userId, userName) {
            if (!confirm('确定要删除用户「' + userName + '」吗？\n\n此操作不可恢复！')) {
                return;
            }

            $.ajax({
                url: shirokiUserConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_delete_user',
                    nonce: shirokiUserConfig.nonce,
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        this.loadUsers();
                    } else {
                        alert(response.data.message || '删除失败');
                    }
                }.bind(this),
                error: function() {
                    alert('网络错误，请稍后重试');
                }
            });
        },

        /**
         * ⏳ 显示加载状态
         */
        showLoading: function() {
            $('#shiroki-user-loading').show();
            $('#shiroki-user-grid').addClass('loading');
        },

        /**
         * ⏳ 隐藏加载状态
         */
        hideLoading: function() {
            $('#shiroki-user-loading').hide();
            $('#shiroki-user-grid').removeClass('loading');
        },

        /**
         * ❌ 显示错误
         */
        showError: function(message) {
            $('#shiroki-user-grid').html('<div class="shiroki-user-empty"><div class="shiroki-user-empty-text">❌ ' + message + '</div></div>');
        },

        /**
         * 🛡️ 转义HTML
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
    $(document).on('shiroki-user-grid-ready', function() {
        UserGridManager.init();
    });

})(jQuery);
