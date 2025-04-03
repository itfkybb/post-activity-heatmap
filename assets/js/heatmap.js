document.addEventListener('DOMContentLoaded', function() {
    console.log('Heatmap Initialized');
    
    const container = document.querySelector('.pah-heatmap-grid');
    if (!container) {
        console.error('Container element not found');
        return;
    }

    // 日期计算
    const today = new Date();
    today.setHours(23, 59, 59, 999);
    const startDate = new Date(pahData.startDate);
    
    // 确保开始日期是周日
    while (startDate.getDay() !== 0) {
        startDate.setDate(startDate.getDate() - 1);
    }
    startDate.setHours(0, 0, 0, 0);

    // 生成周数容器
    const weekColumns = document.createElement('div');
    weekColumns.className = 'pah-week-columns';
    let weekCounter = 0;

    // 生成日期格子
    let currentDate = new Date(startDate);
    const allDays = [];
    
    while (currentDate <= today && weekCounter < 53) {
        // 每周日生成周数
        if (currentDate.getDay() === 0) {
            const weekDiv = document.createElement('div');
            weekDiv.className = 'pah-week-number';
            weekDiv.textContent = ++weekCounter;
            weekColumns.appendChild(weekDiv);
        }

        // 创建日期元素
        const dayElement = createDayElement(currentDate);
        allDays.push(dayElement);
        
        currentDate.setDate(currentDate.getDate() + 1);
    }

    // 插入元素
    container.append(...allDays);
    container.parentNode.insertBefore(weekColumns, container);

    function createDayElement(date) {
        const dayElement = document.createElement('div');
        dayElement.className = 'pah-heatmap-day';
        
        // 日期格式化
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        const dateStr = `${y}-${m}-${d}`;
        
        // 匹配数据
        const countData = pahData.activity.find(item => {
            const dbDate = new Date(item.date);
            return dbDate.toDateString() === date.toDateString();
        });
        const count = countData ? parseInt(countData.count) : 0;

        // 设置数据属性
        dayElement.dataset.level = calculateLevel(count);
        
        // 工具提示
        dayElement.innerHTML = `
            <div class="pah-heatmap-tooltip">
                ${dateStr}<br>
                ${count} ${pahData.labels.posts}${count !== 1 ? 's' : ''}
                ${dateStr === today.toISOString().split('T')[0] ? `(${pahData.labels.today})` : ''}
            </div>
        `;

        // 未来日期处理
        if (date > today) {
            dayElement.classList.add('pah-future');
            dayElement.dataset.level = 0;
        }

        return dayElement;
    }

    function calculateLevel(count) {
        if (count === 0) return 0;
        if (count <= 2) return 1;
        if (count <= 4) return 2;
        if (count <= 6) return 3;
        return 4;
    }
});
