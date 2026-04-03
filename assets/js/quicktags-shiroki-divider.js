/* 🌊 shiroki分割线与换行Quicktags按钮
 * 🕊️白木 原创开发 🔗gl.baimu.live
 */
(function() {
    /* 🔍 查找Quicktags工具栏并初始化按钮 */
    function initShirokiButtons() {
        var toolbar = document.getElementById('ed_toolbar');
        if (!toolbar) return;
        
        /* 🎨 检查是否已经添加了分割线按钮 */
        if (!toolbar.querySelector('#qt_content_shiroki_divider')) {
            /* 🌊 创建分割线按钮 */
            var dividerButton = document.createElement('input');
            dividerButton.type = 'button';
            dividerButton.id = 'qt_content_shiroki_divider';
            dividerButton.className = 'ed_button button button-small';
            dividerButton.value = '分割线';
            dividerButton.title = '插入粉紫蓝渐变波浪分割线';
            
            /* 🎯 添加点击事件 */
            dividerButton.addEventListener('click', function() {
                var textarea = document.getElementById('content');
                if (textarea) {
                    /* 📝 获取光标位置 */
                    var start = textarea.selectionStart;
                    var end = textarea.selectionEnd;
                    var text = textarea.value;
                    
                    /* 🌊 插入分割线HTML注释语法 前后都带换行 */
                    var dividerHtml = '\n<!--shiroki-divider-->\n';
                    textarea.value = text.substring(0, start) + dividerHtml + text.substring(end);
                    
                    /* 🔧 设置新的光标位置 */
                    var newCursorPos = start + dividerHtml.length;
                    textarea.selectionStart = newCursorPos;
                    textarea.selectionEnd = newCursorPos;
                    
                    /* 🎯 触发change事件 */
                    var event = new Event('input', { bubbles: true });
                    textarea.dispatchEvent(event);
                    
                    /* 🎯 聚焦到文本区域 */
                    textarea.focus();
                }
            });
            
            /* 📌 将按钮添加到工具栏末尾 */
            toolbar.appendChild(dividerButton);
        }
        
        /* 💨 检查是否已经添加了换行按钮 */
        if (!toolbar.querySelector('#qt_content_shiroki_nbsp')) {
            /* 💨 创建换行按钮 */
            var nbspButton = document.createElement('input');
            nbspButton.type = 'button';
            nbspButton.id = 'qt_content_shiroki_nbsp';
            nbspButton.className = 'ed_button button button-small';
            nbspButton.value = '换行';
            nbspButton.title = '插入换行符 &nbsp;';
            
            /* 🎯 添加点击事件 */
            nbspButton.addEventListener('click', function() {
                var textarea = document.getElementById('content');
                if (textarea) {
                    /* 📝 获取光标位置 */
                    var start = textarea.selectionStart;
                    var end = textarea.selectionEnd;
                    var text = textarea.value;
                    
                    /* 💨 插入换行符 &nbsp; 前后都带换行 */
                    var nbspHtml = '\n&nbsp;\n';
                    textarea.value = text.substring(0, start) + nbspHtml + text.substring(end);
                    
                    /* 🔧 设置新的光标位置 */
                    var newCursorPos = start + nbspHtml.length;
                    textarea.selectionStart = newCursorPos;
                    textarea.selectionEnd = newCursorPos;
                    
                    /* 🎯 触发change事件 */
                    var event = new Event('input', { bubbles: true });
                    textarea.dispatchEvent(event);
                    
                    /* 🎯 聚焦到文本区域 */
                    textarea.focus();
                }
            });
            
            /* 📌 将按钮添加到工具栏末尾 */
            toolbar.appendChild(nbspButton);
        }
    }
    
    /* 🎯 等待DOM加载完成 */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            /* ⏳ 延迟执行，确保Quicktags已初始化 */
            setTimeout(initShirokiButtons, 500);
        });
    } else {
        /* 🔄 DOM已加载，延迟执行 */
        setTimeout(initShirokiButtons, 500);
    }
    
    /* 🔄 监听DOM变化，确保在动态加载时也能添加按钮 */
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.id === 'ed_toolbar' || (node.querySelector && node.querySelector('#ed_toolbar'))) {
                        setTimeout(initShirokiButtons, 100);
                    }
                });
            }
        });
    });
    
    /* 🔍 观察文档变化 */
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
})();
