/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 🌐 51LA 统计仪表盘交互脚本
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage 51LA_Stats
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 🎯 51LA 统计仪表盘控制器
     */
    var Shiroki51LAStats = {

        /**
         * 📊 图表实例
         */
        chart: null,

        /**
         * 🚀 初始化
         */
        init: function() {
            if (!$('.shiroki-51la-dashboard').length) {
                return;
            }

            this.initChart();
            this.bindEvents();
            this.animateNumbers();
        },

        /**
         * 📈 初始化图表
         */
        initChart: function() {
            var ctx = document.getElementById('shiroki-51la-chart');
            if (!ctx) {
                return;
            }

            var data = window.shiroki51LAData;
            if (!data || !data.labels || data.labels.length === 0) {
                return;
            }

            // 🎨 图表配置
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: '浏览量(PV)',
                            data: data.pv,
                            borderColor: '#63b3ed',
                            backgroundColor: 'rgba(99, 179, 237, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#63b3ed',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: '访客数(UV)',
                            data: data.uv,
                            borderColor: '#48bb78',
                            backgroundColor: 'rgba(72, 187, 120, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#48bb78',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: 'rgba(0, 0, 0, 0.5)',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                color: 'rgba(0, 0, 0, 0.5)',
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    if (value >= 1000) {
                                        return (value / 1000).toFixed(1) + 'k';
                                    }
                                    return value;
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    }
                }
            });

            // 🌙 暗色模式适配
            this.updateChartTheme();
        },

        /**
         * 🌙 更新图表主题
         */
        updateChartTheme: function() {
            if (!this.chart) {
                return;
            }

            var isDark = $('body').hasClass('wp-dark-mode') || $('.wp-dark-mode').length > 0;
            var textColor = isDark ? 'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.5)';
            var gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';

            this.chart.options.scales.x.ticks.color = textColor;
            this.chart.options.scales.y.ticks.color = textColor;
            this.chart.options.scales.y.grid.color = gridColor;
            this.chart.update();
        },

        /**
         * 🔗 绑定事件
         */
        bindEvents: function() {
            var self = this;

            /* 🌙 监听暗色模式变化 */
            $(document).on('click', '[data-wp-dark-mode-toggle]', function() {
                setTimeout(function() {
                    self.updateChartTheme();
                }, 100);
            });

            /* 🔘 表单提交 */
            $(document).on('submit', '.shiroki-51la-config-form', function(e) {
                // 表单正常提交，页面会刷新
                // 这里可以添加额外的验证逻辑
            });

            /* 🗂️ 标签切换 */
            $(document).on('click', '.shiroki-51la-tab', function(e) {
                e.preventDefault();
                var $tab = $(this);
                var tabId = $tab.data('tab');

                // 🔄 切换标签按钮状态
                $('.shiroki-51la-tab').removeClass('active');
                $tab.addClass('active');

                // 🔄 切换内容显示
                $('.shiroki-51la-tab-content').removeClass('active');
                $('[data-tab-content="' + tabId + '"]').addClass('active');

                // 📈 如果切换到数据概览标签，重新渲染图表
                if (tabId === 'overview' && self.chart) {
                    setTimeout(function() {
                        self.chart.resize();
                    }, 100);
                }

                // 🔗 如果切换到外部链接标签，触发动画
                if (tabId === 'external-links') {
                    self.animateExternalStats();
                }
            });

            /* 📅 时间筛选 */
            $(document).on('click', '#time-filter .filter-btn', function() {
                var $btn = $(this);
                var timeRange = $btn.data('time');

                // 🔄 切换按钮状态
                $('#time-filter .filter-btn').removeClass('active');
                $btn.addClass('active');

                // 📅 显示/隐藏自定义日期选择器
                if (timeRange === 'custom') {
                    $('#custom-date-range').slideDown(200);
                } else {
                    $('#custom-date-range').slideUp(200);
                    // 🔄 模拟数据刷新
                    self.refreshExternalData(timeRange);
                }
            });

            /* 📅 应用自定义日期 */
            $(document).on('click', '#apply-custom-date', function() {
                var startDate = $('#date-start').val();
                var endDate = $('#date-end').val();
                if (startDate && endDate) {
                    self.refreshExternalData('custom', startDate, endDate);
                }
            });

            /* 📱 设备类型筛选 - 按钮模式 */
            $(document).on('click', '#device-filter .filter-btn', function() {
                var $btn = $(this);
                var deviceType = $btn.data('device');

                // 🔄 切换按钮状态
                $('#device-filter .filter-btn').removeClass('active');
                $btn.addClass('active');

                // 🔄 筛选数据
                self.filterExternalData('device', deviceType);
            });

            /* 👤 访客类型筛选 - 按钮模式 */
            $(document).on('click', '#visitor-filter .filter-btn', function() {
                var $btn = $(this);
                var visitorType = $btn.data('visitor');

                // 🔄 切换按钮状态
                $('#visitor-filter .filter-btn').removeClass('active');
                $btn.addClass('active');

                // 🔄 筛选数据
                self.filterExternalData('visitor', visitorType);
            });

            /* 📄 分页 */
            $(document).on('click', '.page-btn:not(:disabled)', function() {
                var $btn = $(this);
                if ($btn.hasClass('active')) return;

                var page = $btn.text();
                if (page === '上一页' || page === '下一页') {
                    // 处理上一页/下一页逻辑
                    return;
                }

                // 🔄 更新分页状态
                $('.page-btn').removeClass('active');
                $btn.addClass('active');

                // 🔄 模拟加载数据
                self.loadExternalPage(parseInt(page));
            });

            /* 📥 导出数据 */
            $(document).on('click', '#export-data', function() {
                self.exportExternalData();
            });

            /* 🔗 查看全部外部链接 - 切换到外部链接标签页 */
            $(document).on('click', '.view-more-link', function(e) {
                e.preventDefault();
                var tabId = $(this).data('tab-trigger');

                // 🔄 切换到外部链接标签页
                $('.shiroki-51la-tab').removeClass('active');
                $('.shiroki-51la-tab[data-tab="' + tabId + '"]').addClass('active');

                // 🔄 切换内容显示
                $('.shiroki-51la-tab-content').removeClass('active');
                $('[data-tab-content="' + tabId + '"]').addClass('active');

                // 🎬 触发外部链接统计动画
                self.animateExternalStats();
            });

            /* ⚙️ API 配置卡片折叠/展开 */
            $(document).on('click', '.shiroki-51la-config-header', function(e) {
                // 如果点击的是表单元素，不触发折叠
                if ($(e.target).closest('input, select, button, a').length) {
                    return;
                }

                var $card = $(this).closest('.shiroki-51la-config-card');
                var $icon = $(this).find('.shiroki-51la-toggle-icon');

                if ($card.hasClass('is-collapsed')) {
                    // 展开
                    $card.removeClass('is-collapsed').addClass('is-expanded');
                    $icon.text('▲');
                } else {
                    // 收起
                    $card.removeClass('is-expanded').addClass('is-collapsed');
                    $icon.text('▼');
                }
            });
        },

        /**
         * 🔗 外部链接统计数字动画
         */
        animateExternalStats: function() {
            var self = this;

            $('#external-link-count, #external-ip-count, #external-pv-count, #external-uv-count, #external-new-visitor-count, #external-session-count').each(function() {
                var $this = $(this);
                var text = $this.text().replace(/,/g, '');
                var target = parseInt(text) || 0;

                if (target > 0 && !$this.hasClass('animated')) {
                    $this.addClass('animated');
                    $this.text('0');
                    setTimeout(function() {
                        self.animateNumber($this, target);
                    }, 100);
                }
            });
        },

        /**
         * 🔄 刷新外部链接数据
         */
        refreshExternalData: function(timeRange, startDate, endDate) {
            var self = this;

            // 🔄 显示加载状态
            $('#external-links-table tbody').fadeOut(200, function() {
                // 模拟数据刷新
                setTimeout(function() {
                    // 📝 这里可以添加 AJAX 请求获取真实数据
                    self.animateExternalStats();
                    $('#external-links-table tbody').fadeIn(200);
                }, 300);
            });
        },

        /**
         * 🔍 筛选外部链接数据
         */
        filterExternalData: function(filterType, filterValue) {
            var self = this;

            // 🔄 显示加载状态
            $('#external-links-table tbody').fadeOut(200, function() {
                // 模拟筛选
                setTimeout(function() {
                    // 📝 这里可以添加筛选逻辑
                    $('#external-links-table tbody').fadeIn(200);
                }, 200);
            });
        },

        /**
         * 📄 加载外部链接分页数据
         */
        loadExternalPage: function(page) {
            var self = this;

            // 🔄 显示加载状态
            $('#external-links-table tbody').fadeOut(200, function() {
                // 模拟分页加载
                setTimeout(function() {
                    // 📝 这里可以添加 AJAX 请求获取分页数据
                    // 更新分页信息
                    var startItem = (page - 1) * 10 + 1;
                    var endItem = Math.min(page * 10, 156);
                    $('#page-start').text(startItem);
                    $('#page-end').text(endItem);

                    $('#external-links-table tbody').fadeIn(200);
                }, 300);
            });
        },

        /**
         * 📥 导出外部链接数据
         */
        exportExternalData: function() {
            // 📝 这里可以添加导出逻辑
            alert('数据导出功能开发中...');
        },

        /**
         * 🔢 数字动画
         */
        animateNumbers: function() {
            var self = this;

            $('.stat-value').each(function() {
                var $this = $(this);
                var target = parseInt($this.text().replace(/,/g, '')) || 0;

                if (target > 0) {
                    $this.text('0');
                    setTimeout(function() {
                        self.animateNumber($this, target);
                    }, 300);
                }
            });
        },

        /**
         * 🔢 单个数字动画
         */
        animateNumber: function($element, targetValue) {
            var currentValue = 0;
            var duration = 800;
            var startTime = null;

            $element.addClass('changing');

            function animate(currentTime) {
                if (!startTime) {
                    startTime = currentTime;
                }

                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);

                // 🎯 缓动函数
                var easeOutQuart = 1 - Math.pow(1 - progress, 4);

                var current = Math.floor(targetValue * easeOutQuart);
                $element.text(current.toLocaleString());

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    $element.removeClass('changing');
                }
            }

            requestAnimationFrame(animate);
        },

        /**
         * 📡 AJAX 获取数据
         */
        fetchData: function(endpoint, params) {
            var self = this;

            return $.ajax({
                url: shiroki51LAConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'shiroki_51la_get_data',
                    endpoint: endpoint,
                    params: params,
                    nonce: shiroki51LAConfig.nonce
                }
            });
        },

        /**
         * 🔔 显示提示消息
         */
        showToast: function(message, type) {
            var $toast = $('.shiroki-51la-toast');
            if ($toast.length === 0) {
                $toast = $('<div class="shiroki-51la-toast"><div class="toast-content"></div></div>');
                $('body').append($toast);
            }

            $toast.removeClass('success error').addClass(type);
            $toast.find('.toast-content').text(message);
            $toast.fadeIn(300);

            setTimeout(function() {
                $toast.fadeOut(300);
            }, 3000);
        }
    };

    /**
     * 🚀 初始化 51LA 统计仪表盘
     */
    $(document).ready(function() {
        Shiroki51LAStats.init();
    });

})(jQuery);
