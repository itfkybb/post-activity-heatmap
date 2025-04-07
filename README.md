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
