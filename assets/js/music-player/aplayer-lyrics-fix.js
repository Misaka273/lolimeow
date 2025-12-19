// 🎵 APlayer歌词修复插件 - 基于示例文件核心实现
class APlayerLyricsFix {
    constructor() {
        this.players = [];
        this.init();
    }

    init() {
        // 监听页面加载完成事件
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupPlayers());
        } else {
            this.setupPlayers();
        }

        // 监听动态加载的播放器
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1 && (node.querySelector('.aplayer') || node.classList.contains('aplayer'))) {
                            setTimeout(() => this.setupPlayers(), 100);
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    setupPlayers() {
        const aplayers = document.querySelectorAll('.aplayer');
        aplayers.forEach((aplayerEl, index) => {
            // 避免重复初始化
            if (!aplayerEl.dataset.lyricsFixed) {
                aplayerEl.dataset.lyricsFixed = 'true';
                this.setupPlayer(aplayerEl);
            }
        });
    }

    setupPlayer(aplayerEl) {
        const lrcContainer = aplayerEl.querySelector('.aplayer-lrc');
        const lrcContents = aplayerEl.querySelector('.aplayer-lrc-contents');
        const audio = aplayerEl.querySelector('audio');

        if (!lrcContainer || !lrcContents || !audio) {
            return;
        }

        // 保存原始的歌词
        const originalLrc = lrcContents.innerHTML;
        
        // 解析歌词
        const lyrics = this.parseLyrics(originalLrc);
        
        if (lyrics.length === 0) {
            return;
        }

        // 重构歌词HTML
        this.rebuildLyricsHtml(lrcContents, lyrics);

        // 监听音频播放事件
        let currentLyricIndex = -1;
        let animationFrame = null;

        // 优化的歌词索引查找函数
        const findLyricIndex = (currentTime) => {
            // 二分查找优化，提高性能
            let low = 0;
            let high = lyrics.length - 1;
            let result = -1;
            
            while (low <= high) {
                const mid = Math.floor((low + high) / 2);
                if (lyrics[mid].time <= currentTime) {
                    result = mid;
                    low = mid + 1;
                } else {
                    high = mid - 1;
                }
            }
            
            return result;
        };

        const updateLyrics = () => {
            const currentTime = audio.currentTime;
            const newIndex = findLyricIndex(currentTime);

            // 只有当索引变化时才更新歌词状态
            if (newIndex !== currentLyricIndex) {
                const lines = lrcContents.querySelectorAll('.aplayer-lrc-line');
                
                // 保存旧索引，用于方向判断
                const oldIndex = currentLyricIndex;
                
                // 更新歌词高亮状态
                this.highlightLyric(lrcContents, newIndex);
                
                // 立即滚动到目标位置，确保精确同步
                this.scrollToLyric(lrcContainer, lrcContents, newIndex);
                
                // 当从前往后播放且不是初始状态时，添加向上推动画
                if (newIndex > oldIndex && oldIndex !== -1) {
                    // 移除所有旧的动画类
                    lines.forEach(line => {
                        line.classList.remove('lyric-push-up');
                    });
                    
                    const prevLine = lines[oldIndex];
                    if (prevLine) {
                        // 强制回流以重新触发动画
                        void prevLine.offsetWidth;
                        prevLine.classList.add('lyric-push-up');
                    }
                }
                
                // 更新当前索引
                currentLyricIndex = newIndex;
            }

            // 继续监听，确保进度更新
            animationFrame = requestAnimationFrame(updateLyrics);
        };

        // 立即执行一次，确保初始状态正确
        updateLyrics();

        // 监听播放事件
        audio.addEventListener('play', () => {
            if (!animationFrame) {
                animationFrame = requestAnimationFrame(updateLyrics);
            }
        });

        audio.addEventListener('pause', () => {
            if (animationFrame) {
                cancelAnimationFrame(animationFrame);
                animationFrame = null;
            }
        });

        // 监听timeupdate事件，确保实时同步
        audio.addEventListener('timeupdate', updateLyrics);
        
        // 监听seeked事件，确保跳转后立即同步
        audio.addEventListener('seeked', updateLyrics);
        
        // 监听loadedmetadata事件，确保音频元数据加载后同步
        audio.addEventListener('loadedmetadata', updateLyrics);
    }

    parseLyrics(originalLrc) {
        const lyrics = [];
        const div = document.createElement('div');
        div.innerHTML = originalLrc;
        const lines = div.querySelectorAll('p');

        lines.forEach((line) => {
            const text = line.textContent.trim();
            if (!text) return;

            // 增强的LRC时间戳解析，支持更多格式
            const timeMatches = text.match(/\[(\d{2}):(\d{2})\.(\d{2,3})\]/g) || 
                              text.match(/\[(\d{2}):(\d{2})\]/g) || 
                              [];
            
            if (timeMatches.length > 0) {
                // 获取歌词文本
                const lyricText = text.replace(/\[(\d{2}):(\d{2})(?:\.(\d{2,3}))?\]/g, '').trim();
                if (!lyricText) return;

                // 处理所有时间戳
                timeMatches.forEach((timeMatch) => {
                    // 支持两种格式：[mm:ss.ms] 和 [mm:ss]
                    const timeParts = timeMatch.match(/\[(\d{2}):(\d{2})(?:\.(\d{2,3}))?\]/);
                    if (timeParts) {
                        const minutes = parseInt(timeParts[1]);
                        const seconds = parseInt(timeParts[2]);
                        // 处理毫秒部分，支持可选的毫秒
                        let milliseconds = timeParts[3] ? parseInt(timeParts[3]) : 0;
                        
                        // 统一转换为3位毫秒
                        if (timeParts[3] && timeParts[3].length === 2) {
                            milliseconds *= 10; // 2位毫秒转换为3位
                        }
                        
                        // 精确计算时间，保留3位小数
                        const time = parseFloat((minutes * 60 + seconds + milliseconds / 1000).toFixed(3));
                        
                        lyrics.push({
                            time: time,
                            text: lyricText
                        });
                    }
                });
            }
        });

        // 按时间排序，确保歌词顺序正确
        return lyrics.sort((a, b) => a.time - b.time);
    }

    rebuildLyricsHtml(container, lyrics) {
        container.innerHTML = '';
        
        lyrics.forEach((lyric, index) => {
            const line = document.createElement('p');
            line.className = 'aplayer-lrc-line';
            line.textContent = lyric.text;
            line.dataset.time = lyric.time;
            container.appendChild(line);
        });
    }

    highlightLyric(container, index) {
        const lines = container.querySelectorAll('.aplayer-lrc-line');
        lines.forEach((line, i) => {
            if (i === index) {
                line.classList.add('aplayer-lrc-current');
                line.classList.remove('aplayer-lrc-current-prev', 'aplayer-lrc-current-next');
            } else if (i < index) {
                line.classList.add('aplayer-lrc-current-prev');
                line.classList.remove('aplayer-lrc-current', 'aplayer-lrc-current-next');
            } else {
                line.classList.add('aplayer-lrc-current-next');
                line.classList.remove('aplayer-lrc-current', 'aplayer-lrc-current-prev');
            }
        });
    }

    scrollToLyric(container, contents, index) {
        if (index === -1) return;
        
        const lines = contents.querySelectorAll('.aplayer-lrc-line');
        const activeLine = lines[index];
        
        if (activeLine) {
            // 临时禁用所有歌词行的transform效果，确保获取准确的offsetTop
            const originalTransforms = [];
            lines.forEach(line => {
                originalTransforms.push(line.style.transform);
                line.style.transform = 'none';
            });
            
            // 获取容器和行的尺寸信息
            const containerHeight = container.clientHeight;
            const activeLineHeight = activeLine.clientHeight;
            
            // 计算目标滚动位置，使当前行始终居中
            const targetOffset = activeLine.offsetTop - (containerHeight / 2) + (activeLineHeight / 2);
            
            // 恢复所有歌词行的原始transform
            lines.forEach((line, i) => {
                line.style.transform = originalTransforms[i];
            });
            
            // 确保滚动位置在有效范围内
            const totalLyricsHeight = contents.scrollHeight;
            const maxOffset = Math.max(0, totalLyricsHeight - containerHeight);
            const finalOffset = Math.max(0, Math.min(maxOffset, targetOffset));
            
            // 使用纯scrollTop滚动，避免与CSS transform冲突
            contents.style.transform = 'translateY(0)';
            container.scrollTop = finalOffset;
        }
    }
}

// 初始化歌词修复插件
document.addEventListener('DOMContentLoaded', () => {
    new APlayerLyricsFix();
});
