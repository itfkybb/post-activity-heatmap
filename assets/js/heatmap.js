document。addEventListener('DOMContentLoaded', function() {
    try {
        const container = document.querySelector('.pah-heatmap-container');
        if (!container) {
            console.error('Heatmap container not found');
            return;
        }

        // 服务器时区日期处理
        const today = new Date(pahData.serverToday + 'T23:59:59');
        const startDate = new Date(pahData.startDate);

        // 校准起始日期为周日
        while (startDate.getDay() !== 0) {
            startDate.setDate(startDate.getDate() - 1);
        }
        startDate.setHours(0, 0, 0, 0);

        // 生成所有日期元素
        let currentDate = new Date(startDate);
        const allDays = [];
        
        while (currentDate <= today) {
            const dayElement = createDayElement(new Date(currentDate));
            allDays.push(dayElement);
            currentDate.setDate(currentDate.getDate() + 1);
        }

        // 按周分组（每周7天）
        const weeks = [];
        for (let i = 0; i < allDays.length; i += 7) {
            const weekDays = allDays.slice(i, i + 7);
            const weekColumn = document.createElement('div');
            weekColumn.className = 'pah-heatmap-grid';
            weekColumn.append(...weekDays.reverse()); // 日期从下到上
            weeks.push(weekColumn);
        }

        // 从右向左排列周列
        container.append(...weeks.reverse());

        function createDayElement(date) {
            const dayElement = document.createElement('div');
            dayElement.className = 'pah-heatmap-day';
            
            // 日期格式化
            const dateStr = date.toISOString().split('T')[0];
            const todayStr = today.toISOString().split('T')[0];
            
            // 匹配数据
            const countData = pahData.activity.find(item => {
                const dbDate = new Date(item.date + 'T00:00:00');
                return dbDate.toDateString() === date.toDateString();
            });
            const count = countData ? parseInt(countData.count) : 0;

            // 设置数据属性
            dayElement.dataset.level = calculateLevel(count);
            
            // 工具提示
            // 修改后的工具提示生成逻辑
            dayElement.innerHTML = `
                <div class="pah-heatmap-tooltip">
                    ${dateStr}<br>
                    ${count} ${count === 1 ? pahData.labels.post_singular : pahData.labels.post_plural}
                    ${dateStr === todayStr ? `(${pahData.labels.today})` : ''}
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
            if (count <= 1) return 1;
            if (count <= 3) return 2;
            if (count <= 5) return 3;
            return 4;
        }

    } catch (error) {
        console.error('Heatmap Error:', error);
    }
});
