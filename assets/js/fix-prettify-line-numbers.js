// 🎯 修复Prettify代码块行号显示问题
// 问题：行号到9后自动从0开始，而不是显示为10
// 解决方案：确保CSS计数器方案正常工作，移除冲突的行号属性

(function() {
    // 等待页面加载完成
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixPrettifyLineNumbers);
    } else {
        fixPrettifyLineNumbers();
    }

    function fixPrettifyLineNumbers() {
        // 检查是否存在prettify相关元素
        const prettyprintElements = document.querySelectorAll('.prettyprint.linenums');
        if (prettyprintElements.length === 0) {
            return;
        }

        // 重写PR.prettyPrint函数，确保行号正确生成
        if (window.PR) {
            // 保存原始的prettyPrint函数
            const originalPrettyPrint = window.PR.prettyPrint;
            
            window.PR.prettyPrint = function() {
                // 调用原始函数
                originalPrettyPrint.apply(this, arguments);
                
                // 确保CSS计数器正常工作
                ensureCSSCounterWorks();
            };
        }

        // 立即修复已渲染的行号
        ensureCSSCounterWorks();
    }

    function ensureCSSCounterWorks() {
        // 查找所有带有行号的代码块
        const codeBlocks = document.querySelectorAll('.prettyprint.linenums');
        
        codeBlocks.forEach(function(block) {
            const ol = block.querySelector('ol.linenums');
            if (ol) {
                const lines = ol.querySelectorAll('li');
                
                // 确保CSS计数器正常工作
                ol.style.counterReset = 'line-number';
                ol.style.listStyleType = 'none';
                
                // 修复每个li元素
                lines.forEach(function(line, index) {
                    // 移除冲突的value属性，避免与CSS计数器冲突
                    line.removeAttribute('value');
                    
                    // 移除内联样式，使用CSS中定义的样式
                    line.removeAttribute('style');
                    
                    // 确保行号递增
                    line.style.counterIncrement = 'line-number';
                    
                    // 保持L0-L9的循环样式，用于交替行高亮
                    line.className = `L${index % 10}`;
                });
            }
        });
    }
})();