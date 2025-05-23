# 相册管理系统 README


## 一、系统概述
本系统基于 PHP + MySQL 开发，实现用户授权后访问手机相册/本地文件、文件上传、媒体管理、用户权限控制等功能。前端采用现代 JavaScript 技术，支持响应式设计，适配移动端和桌面端。


## 二、技术栈
### 后端
- **PHP**：用于服务器端逻辑处理和 API 开发
- **MySQL**：存储用户数据、媒体元数据和共享链接
- **Intervention Image**：图片处理库（需通过 Composer 安装）

### 前端
- **HTML5/CSS3**：基础页面结构和样式
- **JavaScript**：实现相册访问、文件上传、动态渲染等功能
- **Tailwind CSS**：快速构建响应式 UI
- **Font Awesome**：图标库


## 三、环境要求
1. **服务器**：
   - PHP 7.4+（需开启 `gd`、`fileinfo` 扩展）
   - MySQL 5.7+ 或 MariaDB
   - Apache/Nginx 服务器（需支持 URL 重写）

2. **客户端**：
   - 支持 `FileSystemAccess API` 的浏览器（如 Chrome 86+、Edge 88+）
   - 移动端建议使用 Chrome 或 Safari


## 四、安装步骤

### 1. 克隆项目
```bash
git clone https://github.com/nzlboss/Album-Management-System.git
cd Album-Management-System
```

### 2. 安装依赖（PHP 库）
```bash
composer install
```

### 3. 配置数据库
#### 创建数据库
```sql
CREATE DATABASE photo_gallery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 修改配置文件
- 复制 `config.sample.php` 为 `config.php`
- 填写数据库连接信息：
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', 'your_password');
  define('DB_NAME', 'photo_gallery');
  ```

### 4. 导入数据库表
```sql
phpmyadmin 或命令行导入 database_schema.sql
```

### 5. 设置目录权限
```bash
chmod -R 755 uploads/       # 上传目录
chmod -R 755 cache/         # 缓存目录（可选）
```


## 五、功能说明

### 1. 用户管理
- **注册/登录**：支持邮箱密码注册，管理员可在后台管理用户
- **权限系统**：
  - **管理员**：拥有所有权限（用户管理、媒体管理）
  - **普通用户**：可上传、管理自己的媒体文件
  - **访客**：仅能通过共享链接访问公开内容

### 2. 媒体管理
- **文件上传**：支持拖放上传、批量选择，自动识别图片/视频类型
- **相册访问**：通过 `FileSystemAccess API` 授权后选择文件（非自动读取）
- **在线预览**：图片直接预览，视频支持播放控制
- **共享功能**：生成带密码保护和过期时间的共享链接

### 3. 系统管理
- **后台仪表盘**：查看媒体统计、用户数、文件类型分布
- **用户管理**：修改用户角色、删除用户及其关联数据
- **安全日志**：记录用户登录、文件访问等操作（需扩展）


## 六、使用指南

### 前端访问
- **本地开发**：直接打开 `index.html`（需服务器环境，可使用 `php -S localhost:8000`）
- **生产环境**：部署到 Web 服务器，访问域名即可

### 管理后台
- 访问路径：`/admin/dashboard.php`
- 初始管理员账号：
  - 邮箱：`admin@9vg.net`
  - 密码：需在 `database_schema.sql` 中修改初始密码哈希值


## 七、扩展与定制

### 1. 功能扩展建议
- **AI 图片识别**：集成 Google Vision 或百度 AI 实现自动标签
- **云存储集成**：支持将文件同步到 AWS S3、七牛云等
- **消息通知**：通过邮件或站内信通知用户文件共享状态

### 2. 代码结构说明
```
├── config.php          # 数据库配置
├── uploads/           # 上传文件存储目录
├── admin/             # 管理后台目录
│   └── dashboard.php  # 后台仪表盘
├── api/               # API 接口目录
│   ├── upload.php     # 文件上传接口
│   └── get_media.php  # 获取媒体列表接口
├── auth/              # 认证模块
│   ├── login.php      # 登录处理
│   └── register.php   # 注册处理
├── middleware/        # 中间件（权限控制）
│   └── auth.php       # 身份验证中间件
└── index.html         # 前端主页面
```


## 八、常见问题

### 1. 文件上传失败
- 检查 `uploads/` 目录是否有写入权限
- 确认 PHP `upload_max_filesize` 和 `post_max_size` 设置足够大（建议至少 50MB）

### 2. 相册访问失败
- 确保使用支持 `FileSystemAccess API` 的浏览器
- 用户必须通过点击按钮等交互触发授权，无法自动调用

### 3. 管理员密码忘记
- 通过 MySQL 直接修改 `users` 表中的 `password_hash` 字段（使用 `password_hash` 生成新哈希值）


## 九、安全注意事项
1. **生产环境建议**：
   - 启用 HTTPS 加密传输
   - 限制 `uploads/` 目录的直接访问
   - 使用 WAF 防护 SQL 注入和 XSS 攻击

2. **密码安全**：
   - 强制用户使用强密码（8 位以上，包含字母、数字、符号）
   - 定期更新管理员密码

3. **文件验证**：
   - 上传文件时进行严格的类型验证（使用 `finfo` 扩展）
   - 避免存储敏感文件，定期清理过期文件


## 十、联系方式
- **作者**：nzlboss
- **邮箱**：qeaf@163.com
- **项目地址**：https://github.com/nzlboss/Album-Management-System
