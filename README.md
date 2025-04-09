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
3. **配色调整**：
   用来表示文章发布数量的颜色值在`assets/css/heatmap.css`中的：

   ```css
      .pah-heatmap-day[data-level="1"] { background: #9be9a8; }
      .pah-heatmap-day[data-level="2"] { background: #40c463; }
      .pah-heatmap-day[data-level="3"] { background: #30a14e; }
      .pah-heatmap-day[data-level="4"] { background: #216e39; }
   ```

   可根据主题配色和个人爱好进行调整。
