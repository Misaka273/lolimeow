/**
 * 🖱️ 自定义鼠标光标控制脚本
 * 白木 🔗gl.baimu.live 开发
 */

(function() {
  'use strict';

  // 🎯 配置对象 - 将由 PHP 注入
  const cursorConfig = window.shirokiCursorConfig || {
    arrow: '',
    handwriting: '',
    ibeam: '',
    appstarting: ''
  };

  const CURSOR_TYPES = [
    { key: 'arrow', cssVar: '--cursor-arrow', fallback: 'auto' },
    { key: 'handwriting', cssVar: '--cursor-handwriting', fallback: 'text' },
    { key: 'ibeam', cssVar: '--cursor-ibeam', fallback: 'text' },
    { key: 'appstarting', cssVar: '--cursor-appstarting', fallback: 'wait' }
  ];

  // 📍 默认热点坐标（32x32 内置光标）
  const DEFAULT_HOTSPOTS = {
    arrow: { x: 5, y: 5 },
    handwriting: { x: 6, y: 24 },
    ibeam: { x: 16, y: 15 },
    appstarting: { x: 5, y: 5 }
  };

  function buildCursorValue(url, x, y, fallback) {
    return `url("${url}") ${x} ${y}, ${fallback}`;
  }

  function detectHotspotFromImage(img, type) {
    const canvas = document.createElement('canvas');
    const width = img.naturalWidth || img.width;
    const height = img.naturalHeight || img.height;

    if (!width || !height) {
      return DEFAULT_HOTSPOTS[type] || { x: 0, y: 0 };
    }

    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0);

    const imageData = ctx.getImageData(0, 0, width, height).data;
    const points = [];

    for (let y = 0; y < height; y++) {
      for (let x = 0; x < width; x++) {
        if (imageData[(y * width + x) * 4 + 3] > 128) {
          points.push({ x, y });
        }
      }
    }

    if (!points.length) {
      return DEFAULT_HOTSPOTS[type] || { x: 0, y: 0 };
    }

    if (type === 'ibeam') {
      let minX = width;
      let maxX = 0;
      let minY = height;
      let maxY = 0;

      points.forEach(function(point) {
        minX = Math.min(minX, point.x);
        maxX = Math.max(maxX, point.x);
        minY = Math.min(minY, point.y);
        maxY = Math.max(maxY, point.y);
      });

      return {
        x: Math.round((minX + maxX) / 2),
        y: Math.round((minY + maxY) / 2)
      };
    }

    if (type === 'handwriting') {
      const maxY = Math.max.apply(null, points.map(function(point) { return point.y; }));
      const bottomPoints = points.filter(function(point) { return point.y >= maxY - 1; });

      return bottomPoints.reduce(function(best, point) {
        return point.x <= best.x ? point : best;
      });
    }

    return points.reduce(function(best, point) {
      return (point.x + point.y) < (best.x + best.y) ? point : best;
    });
  }

  function loadCursorHotspot(url, type) {
    return new Promise(function(resolve) {
      const img = new Image();

      img.onload = function() {
        resolve(detectHotspotFromImage(img, type));
      };

      img.onerror = function() {
        resolve(DEFAULT_HOTSPOTS[type] || { x: 0, y: 0 });
      };

      img.src = url;
    });
  }

  // 🚀 初始化自定义光标
  function initCustomCursor() {
    const body = document.body;

    // ◀️ 添加启用标记类名
    body.classList.add('custom-cursor-enabled');

    // 🎨 设置 CSS 变量（含热点坐标）
    setCursorCSSVariables();

    // ⏳ 监听页面加载状态
    monitorLoadingState();

    // 📝 监听输入框焦点状态
    monitorInputFocus();
  }

  // 🎨 设置光标 CSS 变量
  function setCursorCSSVariables() {
    const root = document.documentElement;

    CURSOR_TYPES.forEach(function(typeConfig) {
      const url = cursorConfig[typeConfig.key];
      if (!url) {
        return;
      }

      const fallbackHotspot = DEFAULT_HOTSPOTS[typeConfig.key] || { x: 0, y: 0 };
      root.style.setProperty(
        typeConfig.cssVar,
        buildCursorValue(url, fallbackHotspot.x, fallbackHotspot.y, typeConfig.fallback)
      );

      loadCursorHotspot(url, typeConfig.key).then(function(hotspot) {
        root.style.setProperty(
          typeConfig.cssVar,
          buildCursorValue(url, hotspot.x, hotspot.y, typeConfig.fallback)
        );
      });
    });
  }

  // ⏳ 监听页面加载状态
  function monitorLoadingState() {
    const body = document.body;
    
    // ◀️ 页面开始加载时显示加载光标
    if (document.readyState === 'loading') {
      body.classList.add('custom-cursor-loading');
    }
    
    // ◀️ DOM 加载完成
    document.addEventListener('DOMContentLoaded', function() {
      // ◀️ 延迟移除加载状态，确保资源开始加载
      setTimeout(() => {
        body.classList.remove('custom-cursor-loading');
      }, 100);
    });
    
    // ◀️ 监听资源加载状态
    let loadingResources = 0;
    
    // 🔍 监听图片加载
    document.addEventListener('load', function(e) {
      if (e.target.tagName === 'IMG' || e.target.tagName === 'IFRAME') {
        checkLoadingComplete();
      }
    }, true);
    
    // 🔍 监听 AJAX 请求
    const originalXHROpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function() {
      loadingResources++;
      body.classList.add('custom-cursor-loading');
      
      this.addEventListener('loadend', function() {
        loadingResources--;
        if (loadingResources <= 0) {
          checkLoadingComplete();
        }
      });
      
      originalXHROpen.apply(this, arguments);
    };
    
    // 🔍 监听 Fetch API
    const originalFetch = window.fetch;
    window.fetch = function() {
      loadingResources++;
      body.classList.add('custom-cursor-loading');
      
      return originalFetch.apply(this, arguments).finally(() => {
        loadingResources--;
        if (loadingResources <= 0) {
          checkLoadingComplete();
        }
      });
    };
    
    // ◀️ 页面完全加载后移除加载状态
    window.addEventListener('load', function() {
      setTimeout(() => {
        body.classList.remove('custom-cursor-loading');
      }, 500);
    });
  }

  // ✅ 检查加载是否完成
  function checkLoadingComplete() {
    setTimeout(() => {
      document.body.classList.remove('custom-cursor-loading');
    }, 200);
  }

  // 📝 监听输入框焦点状态
  function monitorInputFocus() {
    const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="search"], input[type="url"], input[type="tel"], input[type="number"], textarea, [contenteditable="true"]');
    
    inputs.forEach(input => {
      // ◀️ 获得焦点时添加标记
      input.addEventListener('focus', function() {
        this.classList.add('cursor-input-focused');
      });
      
      // ◀️ 失去焦点时移除标记
      input.addEventListener('blur', function() {
        this.classList.remove('cursor-input-focused');
      });
    });
  }

  // 🚀 启动初始化
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCustomCursor);
  } else {
    initCustomCursor();
  }

})();
