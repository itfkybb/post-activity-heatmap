# post-activity-heatmap
在WordPress中显示类似GitHub的博客文章活跃度热力图
### 插件使用说明

1. **安装插件**：

   - 将 `post-activity-heatmap` 文件夹压缩为 ZIP
   - 在WordPress后台：插件 → 安装插件 → 上传插件

2. **使用短代码**：

   ```
   HTML<!-- 在文章/页面中 -->
   [post_heatmap]
   
   <!-- 在模板文件中 -->
   <?php echo do_shortcode('[post_heatmap]'); ?>
   ```

3. **自定义样式**：

   - 修改 `heatmap.css` 中的颜色值：

   ```
   CSS/* 修改基础色 */
   .pah-heatmap-day {
       background-color: #f0f0f0; /* 默认颜色 */
   }
   
   /* 修改色相值（145=绿色） */
   hsl(145, 60%, ...)
   ```

### 扩展建议

1. **添加设置页面**：

   ```
   PHP// 在类中添加：
   public function add_settings_page() {
       add_options_page(
           'Heatmap Settings',
           'Activity Heatmap',
           'manage_options',
           'pah-settings',
           [$this, 'render_settings_page']
       );
   }
   
   public function render_settings_page() {
       // 输出设置表单
   }
   ```

2. **支持自定义时间范围**：

   ```
   PHP// 修改SQL查询中的 $start_date
   $start_date = date('Y-m-d', strtotime('-6 months')); 
   ```

3. **多数据类型支持**：

   ```
   PHP// 扩展查询条件
   WHERE post_type IN ('post', 'custom_post_type')
   ```
