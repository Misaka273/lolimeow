// 🔧 APlayer 歌词逻辑
// 🔗 项目地址：https://github.com/Chuyel/aplayer-lyrics-fix
class APlayerLyricsFix {
    constructor() {
        this.players = [];
        this.init();
    }

    init() {
        // 📌 监听页面加载完成事件
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.setupPlayers();
                // ⏱️ 添加延迟初始化，确保所有动态内容加载完成
                setTimeout(() => this.setupPlayers(), 500);
            });
        } else {
            this.setupPlayers();
            // ⏱️ 添加延迟初始化，确保所有动态内容加载完成
            setTimeout(() => this.setupPlayers(), 500);
        }

        // 🔍 监听动态加载的播放器 - 优化版
        const observer = new MutationObserver((mutations) => {
            let needUpdate = false;
            
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) {
                            // 🎵 检查当前节点或其子节点是否包含播放器
                            if (node.classList.contains('aplayer') || node.querySelector('.aplayer')) {
                                needUpdate = true;
                            }
                        }
                    });
                }
            });
            
            if (needUpdate) {
                // ⏱️ 使用setTimeout确保DOM已渲染完成
                setTimeout(() => this.setupPlayers(), 150);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    setupPlayers() {
        const aplayers = document.querySelectorAll('.aplayer');
        aplayers.forEach((aplayerEl, index) => {
            // 🔒 避免重复初始化
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
        
        // 🎯 定义歌词更新函数，用于初始化和歌曲切换时调用
        const updateLyricsContent = () => {
            // 💾 保存当前的歌词
            const currentLrc = lrcContents.innerHTML;
            
            // 📝 解析歌词
            const lyrics = this.parseLyrics(currentLrc);
            
            if (lyrics.length === 0) {
                // 📝 如果解析失败，尝试从data-lrc属性获取歌词
                const aplayerMain = aplayerEl.querySelector('.aplayer-main');
                if (aplayerMain && aplayerMain.dataset.lrc) {
                    const dataLrc = aplayerMain.dataset.lrc;
                    const dataLyrics = this.parseLyrics(dataLrc);
                    if (dataLyrics.length > 0) {
                        // 🔄 更新歌词内容
                        this.updateLyricsContent(lrcContents, dataLyrics);
                    }
                }
                return;
            }
            
            // 🔄 更新歌词内容
            this.updateLyricsContent(lrcContents, lyrics);
        };
        
        // 🎨 确保lrcContainer有固定高度和正确的overflow属性
        lrcContainer.style.height = '140px';
        lrcContainer.style.overflow = 'auto';
        
        // 🚀 初始更新歌词
        updateLyricsContent();
        
        // 🔄 监听歌曲切换事件 - 观察DOM变化，处理歌词更新
        const lrcObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    // ⏱️ 延迟更新，确保新歌词完全加载
                    setTimeout(() => updateLyricsContent(), 100);
                }
            });
        });
        
        // 📡 观察歌词内容的变化
        lrcObserver.observe(lrcContents, {
            childList: true,
            characterData: true,
            subtree: true
        });
        
        // 📡 也观察aplayer-main的data-lrc属性变化
        const aplayerMain = aplayerEl.querySelector('.aplayer-main');
        if (aplayerMain) {
            lrcObserver.observe(aplayerMain, {
                attributes: true,
                attributeFilter: ['data-lrc']
            });
        }
        
        // 💾 保存observer引用，以便在需要时取消观察
        aplayerEl.dataset.lrcObserver = lrcObserver;

        // 🎵 监听音频播放事件
        let currentLyricIndex = 0;
        let animationFrame = null;
        
        // 🖱️ 用户滚动控制
        let isUserScrolling = false;
        let userScrollTimeout = null;
        const USER_SCROLL_DELAY = 2000; // ⏱️ 用户手动滚动后，暂停自动滚动2秒
        let programmaticScroll = false; // 📍 标记是否是程序自动滚动
        
        // 🖱️ 监听用户手动滚动事件
        lrcContainer.addEventListener('scroll', () => {
            // 📍 如果是程序自动滚动，忽略该事件
            if (programmaticScroll) {
                return;
            }
            
            // 🔖 标记为用户手动滚动
            isUserScrolling = true;
            
            // ⏱️ 清除之前的超时定时器
            if (userScrollTimeout) {
                clearTimeout(userScrollTimeout);
            }
            
            // ⏱️ 延迟恢复自动滚动
            userScrollTimeout = setTimeout(() => {
                isUserScrolling = false;
                // 📍 恢复自动滚动时，确保当前歌词居中
                setScrollPosition(currentLyricIndex);
            }, USER_SCROLL_DELAY);
        }, { passive: true });
        
        // 🔍 索引层：使用线性搜索找到当前歌词行号（更准确）
        const findIndex = (ts) => {
            // 📋 获取所有歌词行
            const lines = lrcContents.querySelectorAll('.aplayer-lrc-line');
            if (lines.length === 0) {
                return 0;
            }
            
            // 🔍 线性搜索，找到最后一个时间戳小于等于当前时间的歌词
            let bestIndex = 0;
            for (let i = 0; i < lines.length; i++) {
                const line = lines[i];
                const time = parseInt(line.dataset.time || 0);
                if (time <= ts) {
                    bestIndex = i;
                } else {
                    break;
                }
            }
            return bestIndex;
        };
        
        // 🎨 更新高亮状态
        const updateHighlight = (index) => {
            const lines = lrcContents.querySelectorAll('.aplayer-lrc-line');
            
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
        };
        
        // 📍 计算并设置滚动位置，确保当前歌词垂直居中，避免累积偏移
        const setScrollPosition = (index) => {
            // 📋 获取所有歌词行
            const lines = lrcContents.querySelectorAll('.aplayer-lrc-line');
            
            if (index < 0 || index >= lines.length) {
                return;
            }
            
            // 🔄 重置lrcContents的transform，避免与scrollTop冲突
            lrcContents.style.transform = 'none';
            lrcContents.style.transition = 'none'; // ⏱️ 禁用CSS过渡，使用JavaScript直接控制滚动
            
            // 🎨 确保lrcContainer有正确的样式
            lrcContainer.style.overflow = 'auto';
            lrcContainer.style.height = '140px';
            lrcContainer.style.position = 'relative';
            
            // 🎨 确保lrcContents有足够的高度，产生滚动条
            lrcContents.style.position = 'relative';
            lrcContents.style.height = 'auto';
            
            // 🎵 获取当前歌词行
            const currentLine = lines[index];
            
            // 📏 获取容器和当前行的基本尺寸
            const containerHeight = lrcContainer.clientHeight;
            const lineHeight = currentLine.clientHeight;
            
            // 📐 使用getBoundingClientRect()获取更准确的位置信息
            // 📏 计算当前行在视口中的位置
            const containerRect = lrcContainer.getBoundingClientRect();
            const lineRect = currentLine.getBoundingClientRect();
            
            // 📐 计算当前行相对于容器的垂直偏移
            const lineOffset = lineRect.top - containerRect.top;
            
            // 📐 计算需要滚动的距离，使当前行居中
            // 🎯 目标位置：容器高度的一半 - 当前行高度的一半
            const targetOffset = containerHeight / 2 - lineHeight / 2;
            const scrollDelta = lineOffset - targetOffset;
            
            // 📐 计算目标scrollTop
            const targetScrollTop = lrcContainer.scrollTop + scrollDelta;
            
            // 🎯 处理边界情况
            const maxScrollTop = Math.max(0, lrcContents.scrollHeight - containerHeight);
            
            // 🎯 确保滚动位置在有效范围内
            const finalScrollTop = Math.max(0, Math.min(maxScrollTop, targetScrollTop));
            
            // 📍 标记为程序自动滚动，避免触发用户滚动检测
            programmaticScroll = true;
            
            // 📍 直接设置scrollTop，避免平滑滚动可能带来的偏移
            lrcContainer.scrollTop = finalScrollTop;
            
            // ⏱️ 动画结束，立即标记为非程序滚动
            setTimeout(() => {
                programmaticScroll = false;
            }, 50);
            
            
        };
        
        // ⚡ 播放层：使用requestAnimationFrame每16ms读取一次时间
        const updateLyricsPlayback = () => {
            // ⏱️ 获取当前时间（秒），转换为毫秒
            const currentTime = audio.currentTime;
            const currentMs = currentTime * 1000;
            
            // 🔍 索引层：找到当前歌词索引
            const newIndex = findIndex(currentMs);
            
            // 🎨 更新高亮状态（始终更新，确保用户能看到当前播放的歌词）
            updateHighlight(newIndex);
            
            // 📍 无论索引是否变化，都确保当前歌词居中显示
            // 🖱️ 只有当不是用户手动滚动时，才自动滚动到目标位置
            if (!isUserScrolling) {
                // 📍 设置滚动位置，确保当前歌词居中
                setScrollPosition(newIndex);
            }
            
            // 🔄 更新当前索引
            currentLyricIndex = newIndex;
            
            // ⚡ 继续监听，确保进度更新
            animationFrame = requestAnimationFrame(updateLyricsPlayback);
        };
        
        // 🚀 立即执行一次，确保初始状态正确
        updateLyricsPlayback();

        // ▶️ 监听play事件
        audio.addEventListener('play', () => {
            if (!animationFrame) {
                animationFrame = requestAnimationFrame(updateLyricsPlayback);
            }
        });

        // ⏸️ 监听pause事件
        audio.addEventListener('pause', () => {
            if (animationFrame) {
                cancelAnimationFrame(animationFrame);
                animationFrame = null;
            }
        });

        // 🔀 监听seeked事件，确保跳转后立即同步
        audio.addEventListener('seeked', () => {
            // ⚡ 取消当前动画帧，立即执行一次更新
            if (animationFrame) {
                cancelAnimationFrame(animationFrame);
                animationFrame = null;
            }
            // ⚡ 立即更新
            updateLyricsPlayback();
        });
        
        // 📊 监听loadedmetadata事件，确保音频元数据加载后同步
        audio.addEventListener('loadedmetadata', () => {
            // ⚡ 取消当前动画帧，立即执行一次更新
            if (animationFrame) {
                cancelAnimationFrame(animationFrame);
                animationFrame = null;
            }
            // ⚡ 立即更新
            updateLyricsPlayback();
        });
        
        // ⚡ 监听playbackratechange事件，确保播放速率调整时歌词仍能准确同步
        audio.addEventListener('playbackratechange', () => {
            // ⚡ 立即更新
            updateLyricsPlayback();
        });
    }

    parseLyrics(originalLrc) {
        const lyrics = [];
        
        // 🎯 首先尝试直接解析原始文本，处理不同API可能返回的不同格式
        let lyricText = originalLrc;
        
        // 🔍 检查是否包含HTML标签，如果包含则提取纯文本
        if (originalLrc.includes('<p>')) {
            const div = document.createElement('div');
            div.innerHTML = originalLrc;
            lyricText = div.textContent || div.innerText || '';
        }
        
        // 📝 按行分割歌词
        const lines = lyricText.split(/[\n\r]+/);
        
        lines.forEach((line, lineIndex) => {
            line = line.trim();
            if (!line) {
                return;
            }
            
            // 🚨 跳过标题行，如[ti:歌曲名]、[ar:歌手名]等
            if (/^\[(ti|ar|al|by|offset):/.test(line)) {
                return;
            }

            // 🔍 增强的LRC时间戳解析，支持更多格式
            // 🎯 支持：[mm:ss.xxx]、[mm:ss.xx]、[mm:ss.x]、[mm:ss]
            const timeMatches = line.match(/\[(\d{1,2}):(\d{2})(?:\.(\d{1,3}))?\]/g) || [];
            
            if (timeMatches.length > 0) {
                // 📝 获取歌词文本
                const lyricText = line.replace(/\[(\d{1,2}):(\d{2})(?:\.(\d{1,3}))?\]/g, '').trim();
                if (!lyricText) {
                    return;
                }

                // ⏱️ 处理所有时间戳
                timeMatches.forEach((timeMatch, matchIndex) => {
                    // ⏱️ 支持多种时间格式：[mm:ss.xxx]、[mm:ss.xx]、[mm:ss.x]、[mm:ss]
                    const timeParts = timeMatch.match(/\[(\d{1,2}):(\d{2})(?:\.(\d{1,3}))?\]/);
                    if (timeParts) {
                        const minutes = parseInt(timeParts[1]);
                        const seconds = parseInt(timeParts[2]);
                        // ⏱️ 处理毫秒部分，支持1-3位毫秒
                        let milliseconds = timeParts[3] ? parseInt(timeParts[3]) : 0;
                        
                        // ⏱️ 统一转换为3位毫秒，确保精度一致
                        if (timeParts[3]) {
                            if (timeParts[3].length === 1) {
                                milliseconds *= 100; // ⏱️ 1位毫秒转换为3位
                            } else if (timeParts[3].length === 2) {
                                milliseconds *= 10; // ⏱️ 2位毫秒转换为3位
                            }
                        }
                        
                        // ⏱️ 转换为毫秒整数做key
                        const time = minutes * 60 * 1000 + seconds * 1000 + milliseconds;
                        
                        lyrics.push({
                            time: time,
                            text: lyricText
                        });
                    }
                });
            }
        });

        // 🔄 按时间排序，确保歌词顺序正确
        const sortedLyrics = lyrics.sort((a, b) => a.time - b.time);
        
        // 🎯 去重，避免重复的歌词行
        const uniqueLyrics = [];
        const seenTimes = new Set();
        
        sortedLyrics.forEach(lyric => {
            if (!seenTimes.has(lyric.time)) {
                seenTimes.add(lyric.time);
                uniqueLyrics.push(lyric);
            }
        });
        
        return uniqueLyrics;
    }

    // 📋 更新歌词内容，添加顶部和底部空白区域以实现顶部歌词居中
    updateLyricsContent(container, lyrics) {
        // 🧹 清空容器
        container.innerHTML = '';
        
        // 📏 获取歌词容器高度，用于计算顶部和底部空白区域
        const parentContainer = container.parentElement;
        const containerHeight = parentContainer ? parentContainer.clientHeight : 200;
        
        // 📏 添加顶部空白区域，高度为容器高度的一半
        // 📏 这样顶部歌词也能在滚动时居中显示
        const topPadding = document.createElement('div');
        topPadding.className = 'aplayer-lrc-padding';
        topPadding.style.height = `${containerHeight / 1}px`;
        topPadding.style.margin = '0';
        topPadding.style.padding = '0';
        container.appendChild(topPadding);
        
        // 📋 重新创建p标签结构
        lyrics.forEach((lyric, index) => {
            const p = document.createElement('p');
            p.className = 'aplayer-lrc-line';
            p.textContent = lyric.text;
            p.dataset.time = lyric.time;
            
            // 📋 添加到容器
            container.appendChild(p);
        });
        
        // 📏 添加底部空白区域，高度为容器高度的一半
        // 📏 这样底部歌词也能在滚动时居中显示
        const bottomPadding = document.createElement('div');
        bottomPadding.className = 'aplayer-lrc-padding';
        bottomPadding.style.height = `${containerHeight / 2}px`;
        bottomPadding.style.margin = '0';
        bottomPadding.style.padding = '0';
        container.appendChild(bottomPadding);
        
        // 🎨 设置初始高亮
        if (lyrics.length > 0) {
            const firstLine = container.querySelector('.aplayer-lrc-line');
            if (firstLine) {
                firstLine.classList.add('aplayer-lrc-current');
            }
        }
    }
}

// 🚀 初始化歌词修复插件
document.addEventListener('DOMContentLoaded', () => {
    new APlayerLyricsFix();
});