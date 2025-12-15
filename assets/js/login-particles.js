// 🌌 背景粒子效果
// 🕸️ 节点连线动画
// 🖱️ 鼠标交互
// ✨ 闪烁波点 (星星)
// 🚀 升空粒子 (流星)

(function() {
    'use strict';

    // 配置参数
    const CONFIG = {
        particleCount: 60,       // 粒子数量 ⬅️ 减少数量
        starCount: 20,           // 🌟 闪烁波点数量 (减少回20)
        risingStarCount: 30,     // 🚀 升空粒子数量 (减少回30)
        colorPalette: [          // 🎨 多彩配色方案
            '255, 182, 193',     // LightPink
            '173, 216, 230',     // LightBlue
            '221, 160, 221',     // Plum
            '144, 238, 144',     // LightGreen
            '255, 218, 185',     // PeachPuff
            '238, 130, 238',     // Violet
            '64, 224, 208'       // Turquoise
        ],
        particleAlpha: 0.5,      // 粒子透明度 ⬅️ 降低透明度
        lineAlpha: 0.4,          // 连线透明度 ⬅️ 降低透明度
        particleSpeed: 1,        // 移动速度
        connectDistance: 150,    // 连线距离
        mouseInteractionDistance: 200, // 鼠标交互距离
        zIndex: 0               // 层级 ⬅️ 位于背景之上，卡片之下
    };

    let canvas, ctx;
    let particles = [];
    let stars = []; // ⬅️ 存放闪烁波点
    let risingStars = []; // ⬅️ 存放升空粒子
    let mouse = { x: null, y: null };

    // 初始化
    function init() {
        // 创建 Canvas
        canvas = document.createElement('canvas');
        canvas.id = 'particle-canvas';
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.style.zIndex = CONFIG.zIndex;
        canvas.style.pointerEvents = 'none'; // ⬅️ 不阻挡下方元素点击
        document.body.appendChild(canvas);

        ctx = canvas.getContext('2d');

        // 设置尺寸
        resize();
        window.addEventListener('resize', resize);

        // 鼠标监听
        window.addEventListener('mousemove', (e) => {
            mouse.x = e.x;
            mouse.y = e.y;
        });
        window.addEventListener('mouseleave', () => {
            mouse.x = null;
            mouse.y = null;
        });

        // 创建元素
        createParticles();
        createStars(); // ⬅️ 创建星星
        createRisingStars(); // ⬅️ 创建升空粒子

        // 开始动画
        animate();
    }

    function resize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }

    // 🕸️ 网络粒子类
    class Particle {
        constructor() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.vx = (Math.random() - 0.5) * CONFIG.particleSpeed;
            this.vy = (Math.random() - 0.5) * CONFIG.particleSpeed;
            this.size = Math.random() * 2 + 1;
            // 🎨 随机分配颜色
            this.color = CONFIG.colorPalette[Math.floor(Math.random() * CONFIG.colorPalette.length)];
        }

        update() {
            this.x += this.vx;
            this.y += this.vy;

            // 边界反弹
            if (this.x < 0 || this.x > canvas.width) this.vx *= -1;
            if (this.y < 0 || this.y > canvas.height) this.vy *= -1;

            // 鼠标交互 (排斥效果)
            if (mouse.x != null) {
                let dx = mouse.x - this.x;
                let dy = mouse.y - this.y;
                let distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < CONFIG.mouseInteractionDistance) {
                    const forceDirectionX = dx / distance;
                    const forceDirectionY = dy / distance;
                    const force = (CONFIG.mouseInteractionDistance - distance) / CONFIG.mouseInteractionDistance;
                    const directionX = forceDirectionX * force * 2; // ⬅️ 排斥力度
                    const directionY = forceDirectionY * force * 2;

                    this.x -= directionX;
                    this.y -= directionY;
                }
            }
        }

        draw() {
            ctx.fillStyle = `rgba(${this.color}, ${CONFIG.particleAlpha})`;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    // ✨ 闪烁波点 (星星) 类
    class Star {
        constructor() {
            this.reset(true); // true 表示初始化，随机初始透明度
        }

        reset(isInit = false) {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 3 + 2; // ⬅️ 波点大小 2-5px
            this.color = CONFIG.colorPalette[Math.floor(Math.random() * CONFIG.colorPalette.length)];
            this.fadeSpeed = Math.random() * 0.015 + 0.005; // ⬅️ 随机闪烁速度
            
            if (isInit) {
                this.alpha = Math.random();
                this.fadeDirection = Math.random() > 0.5 ? 1 : -1;
            } else {
                this.alpha = 0;
                this.fadeDirection = 1; // 必须是从隐到显
            }
        }

        update() {
            if (this.fadeDirection === 1) {
                this.alpha += this.fadeSpeed;
                if (this.alpha >= 1) {
                    this.alpha = 1;
                    this.fadeDirection = -1;
                }
            } else {
                this.alpha -= this.fadeSpeed;
                if (this.alpha <= 0) {
                    this.alpha = 0;
                    this.reset(); // ⬅️ 消失后重置位置
                }
            }
        }

        draw() {
            ctx.fillStyle = `rgba(${this.color}, ${this.alpha})`;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
            
            // 🌟 可选：添加一点发光效果
            ctx.shadowBlur = 5;
            ctx.shadowColor = `rgba(${this.color}, ${this.alpha})`;
            ctx.fill();
            ctx.shadowBlur = 0; // 重置
        }
    }

    // 🚀 升空粒子 (流星) 类
    class RisingStar {
        constructor() {
            this.reset(true);
        }

        reset(isInit = false) {
            this.x = Math.random() * canvas.width;
            if (isInit) {
                this.y = Math.random() * canvas.height;
            } else {
                this.y = canvas.height + Math.random() * 100; // 从底部下方开始
            }
            this.speed = Math.random() * 0.5 + 0.2; // 上升速度
            this.size = Math.random() * 2 + 0.5; // 较小的尺寸
            this.color = CONFIG.colorPalette[Math.floor(Math.random() * CONFIG.colorPalette.length)];
            this.maxAlpha = Math.random() * 0.5 + 0.3; // 最大透明度
        }

        update() {
            this.y -= this.speed;

            // 渐隐渐显计算：基于高度的抛物线透明度
            // y: canvas.height -> 0
            // progress: 0 -> 1
            const progress = (canvas.height - this.y) / canvas.height;
            
            // 透明度曲线：两头低，中间高 (sin(0) -> sin(PI))
            // 加上随机因子让闪烁更自然
            this.alpha = Math.sin(progress * Math.PI) * this.maxAlpha;

            // 越界重置
            if (this.y < -10) {
                this.reset();
            }
        }

        draw() {
            if (this.alpha <= 0) return;
            ctx.fillStyle = `rgba(${this.color}, ${this.alpha})`;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function createParticles() {
        particles = [];
        // 根据屏幕面积调整粒子数量，避免小屏幕太拥挤或大屏幕太稀疏
        const area = canvas.width * canvas.height;
        const count = Math.floor(area / 15000); // ⬅️ 密度系数
        const finalCount = Math.min(Math.max(count, 50), 200); // 限制在 50-200 之间
        
        for (let i = 0; i < finalCount; i++) {
            particles.push(new Particle());
        }
    }

    function createStars() {
        stars = [];
        for (let i = 0; i < CONFIG.starCount; i++) {
            stars.push(new Star());
        }
    }

    function createRisingStars() {
        risingStars = [];
        for (let i = 0; i < CONFIG.risingStarCount; i++) {
            risingStars.push(new RisingStar());
        }
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // 1. 绘制网络粒子
        for (let i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();

            // 绘制连线
            for (let j = i; j < particles.length; j++) {
                let dx = particles[i].x - particles[j].x;
                let dy = particles[i].y - particles[j].y;
                let distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < CONFIG.connectDistance) {
                    ctx.beginPath();
                    let opacity = 1 - (distance / CONFIG.connectDistance);
                    
                    // 🌈 创建渐变连线
                    const gradient = ctx.createLinearGradient(particles[i].x, particles[i].y, particles[j].x, particles[j].y);
                    gradient.addColorStop(0, `rgba(${particles[i].color}, ${opacity * CONFIG.lineAlpha})`);
                    gradient.addColorStop(1, `rgba(${particles[j].color}, ${opacity * CONFIG.lineAlpha})`);
                    
                    ctx.strokeStyle = gradient;
                    ctx.lineWidth = 1;
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.stroke();
                }
            }
        }

        // 2. 绘制闪烁波点
        for (let i = 0; i < stars.length; i++) {
            stars[i].update();
            stars[i].draw();
        }

        // 3. 绘制升空粒子 ⬅️ 新增
        for (let i = 0; i < risingStars.length; i++) {
            risingStars[i].update();
            risingStars[i].draw();
        }

        requestAnimationFrame(animate);
    }

    // 启动
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
