// 📋 统一复制功能脚本
// 确保只在DOM完全加载后执行
document.addEventListener('DOMContentLoaded', function() {
    // 使用事件委托，监听所有复制按钮的点击事件
    document.addEventListener('click', function(e) {
        // 检查点击的元素是否是复制按钮或其子元素
        const copyBtn = e.target.closest('.copy-btn');
        if (copyBtn) {
            e.preventDefault();
            e.stopPropagation();
            
            // 获取要复制的文本
            const copyText = copyBtn.getAttribute('data-copy-text');
            if (!copyText) return;
            
            // 定义复制成功后的回调函数
            function copySuccess() {
                // 检查showToast函数是否存在
                if (typeof showToast === 'function') {
                    // 使用主题已实现的showToast函数显示复制成功提示，传递实际复制的文本
                    showToast(copyText, true);
                } else {
                    // 如果showToast函数不存在，使用alert提示
                    alert('已复制：' + copyText);
                }
            }
            
            // 定义复制失败后的回调函数
            function copyFail() {
                alert('复制失败，请手动复制');
            }
            
            // 优先使用Clipboard API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(copyText)
                    .then(copySuccess)
                    .catch(function(err) {
                        console.error('Clipboard API error:', err);
                        // 降级使用传统方法
                        fallbackCopyTextToClipboard(copyText, copySuccess, copyFail);
                    });
            } else {
                // 直接使用传统方法
                fallbackCopyTextToClipboard(copyText, copySuccess, copyFail);
            }
        }
    });
    
    // 传统复制方法，兼容不支持Clipboard API的浏览器
    function fallbackCopyTextToClipboard(text, successCallback, failCallback) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        
        // 设置样式，避免影响页面布局
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        textArea.style.opacity = '0';
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                successCallback();
            } else {
                failCallback();
            }
        } catch (err) {
            console.error('execCommand error:', err);
            failCallback();
        } finally {
            document.body.removeChild(textArea);
        }
    }
});
