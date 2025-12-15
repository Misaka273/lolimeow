jQuery(document).ready(function($) {
    // 🎨 扁平圆角风格 Select 模拟器
    // 仅针对非多选、非隐藏、可见的 Select 元素进行美化
    // 排除特定插件可能冲突的区域
    
    function initBoxmoeSelect() {
        $('select:not([multiple]):not(.boxmoe-select-hidden)').each(function() {
            var $this = $(this);
            
            // 排除已经被其他插件美化过的 select (如 select2)
            if ($this.hasClass('select2-hidden-accessible') || $this.hasClass('chosen-select')) {
                return;
            }

            // 获取当前选中的选项文本
            var selectedText = $this.find('option:selected').text();
            
            // 获取原生 Select 的宽度（在隐藏之前）
            var originWidth = $this.outerWidth();
            var originStyleWidth = $this[0].style.width;

            // 1. 隐藏原生 Select
            $this.addClass('boxmoe-select-hidden');
            
            // 2. 创建包裹容器
            var $wrapper = $('<div class="boxmoe-select-wrapper"></div>');
            
            // 设置宽度：优先使用计算宽度，确保与原生一致
            if (originStyleWidth) {
                 $wrapper.css('width', originStyleWidth);
            } else if (originWidth > 0) {
                 // 稍微增加一点缓冲，因为模拟框的 padding 可能不同
                 $wrapper.css('width', originWidth + 20 + 'px');
            } else {
                 $wrapper.css('min-width', '80px'); // 兜底最小宽度
            }

            $this.after($wrapper);
            $wrapper.append($this);
            
            // 3. 创建显示框 (Trigger)
            var $trigger = $('<div class="boxmoe-select-trigger"></div>');
            $trigger.text(selectedText);
            $wrapper.append($trigger);
            
            // 4. 创建下拉列表 (Dropdown)
            var $dropdown = $('<div class="boxmoe-select-dropdown"></div>');
            var $list = $('<ul></ul>');
            
            $this.find('option').each(function() {
                var $option = $(this);
                var $li = $('<li></li>');
                $li.text($option.text());
                $li.attr('data-value', $option.val());
                
                if ($option.is(':selected')) {
                    $li.addClass('selected');
                }
                
                $list.append($li);
            });
            
            $dropdown.append($list);
            $wrapper.append($dropdown);
            
            // 5. 事件绑定
            
            // 点击 Trigger 切换下拉显示
            $trigger.on('click', function(e) {
                e.stopPropagation();
                
                // 关闭其他已打开的下拉
                $('.boxmoe-select-wrapper.open').not($wrapper).removeClass('open');
                
                $wrapper.toggleClass('open');
            });
            
            // 点击选项
            $list.on('click', 'li', function(e) {
                e.stopPropagation();
                var $li = $(this);
                var value = $li.attr('data-value');
                var text = $li.text();
                
                // 更新 Trigger 文本
                $trigger.text(text);
                
                // 更新下拉选中状态
                $list.find('li.selected').removeClass('selected');
                $li.addClass('selected');
                
                // 同步到原生 Select 并触发 change 事件
                $this.val(value).trigger('change');
                
                // 关闭下拉
                $wrapper.removeClass('open');
            });
            
            // 点击外部关闭
            $(document).on('click', function() {
                $wrapper.removeClass('open');
            });

            // 监听原生 Select 的 change 事件（如果是外部触发的）
            $this.on('change', function() {
                var newText = $(this).find('option:selected').text();
                $trigger.text(newText);
                var newVal = $(this).val();
                $list.find('li').removeClass('selected');
                $list.find('li[data-value="' + newVal + '"]').addClass('selected');
            });
        });
    }

    // 初始化
    initBoxmoeSelect();
    
    // 监听 Ajax 完成事件 (针对部分动态加载的 select)
    $(document).ajaxComplete(function() {
        setTimeout(initBoxmoeSelect, 500);
    });
});