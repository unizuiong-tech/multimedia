SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 将 project_db 替换为具体项目对应的数据库名
CREATE DATABASE IF NOT EXISTS project_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE project_db;

-- 1. 标签字典表（标准主标签，支持按维度轻量分组）
CREATE TABLE tags (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '标签ID',
  group_code VARCHAR(32) NOT NULL DEFAULT 'general' COMMENT '标签分组: general通用 / genre题材 / mood看点 / region地区',
  name VARCHAR(50) NOT NULL COMMENT '标准标签名称，如: 悬疑 / 甜宠 / 搞笑 / 完结',
  sort_order INT NOT NULL DEFAULT 0 COMMENT '同组内展示排序(步长10)',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  UNIQUE KEY uk_group_name (group_code, name),
  KEY idx_group_sort (group_code, sort_order, id),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='标签字典表';

-- 2. 标签同义词映射表（将同义词/别名归并到标准主标签）
CREATE TABLE tag_aliases (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '映射ID',
  group_code VARCHAR(32) NOT NULL DEFAULT 'general' COMMENT '所属分组，与 tags.group_code 对齐',
  alias_name VARCHAR(50) NOT NULL COMMENT '同义词/别名，如: 沙雕 / 撒糖',
  canonical_tag_id BIGINT UNSIGNED NOT NULL COMMENT '归并到的标准主标签ID(对应 tags.id)',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  UNIQUE KEY uk_group_alias (group_code, alias_name),
  KEY idx_canonical_tag (canonical_tag_id),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='标签同义词映射表';

-- 3. 作品与标准标签关联表
CREATE TABLE work_tags (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '关联ID',
  work_id BIGINT UNSIGNED NOT NULL COMMENT '作品ID(对应 works.id)',
  tag_id BIGINT UNSIGNED NOT NULL COMMENT '标准标签ID(对应 tags.id)',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '关联时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  UNIQUE KEY uk_work_tag (work_id, tag_id),
  KEY idx_tag_work (tag_id, work_id),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='作品标签关联表';

-- 1. 作品主表
CREATE TABLE works (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '作品ID',
  admin_id BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建/操作管理员ID',
  content_type VARCHAR(16) NOT NULL COMMENT '内容类型: video / comic / novel / tv_series / short_drama',
  title VARCHAR(200) NOT NULL COMMENT '作品标题',
  sub_title VARCHAR(200) NOT NULL DEFAULT '' COMMENT '副标题/别名',
  cover_path VARCHAR(255) NOT NULL DEFAULT '' COMMENT '当前封面路径',
  author VARCHAR(100) NOT NULL DEFAULT '' COMMENT '作者/UP主/制作方',
  summary VARCHAR(2000) NOT NULL DEFAULT '' COMMENT '作品简介',
  publish_status TINYINT NOT NULL DEFAULT 0 COMMENT '发布状态: 0草稿 1连载中 2已完结 3已下架',
  chapter_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '已发布章节/分集总数',
  latest_chapter_id BIGINT UNSIGNED NULL COMMENT '最新章节/分集ID',
  latest_chapter_title VARCHAR(200) NOT NULL DEFAULT '' COMMENT '最新章节/分集标题',
  latest_chapter_at DATETIME NULL COMMENT '最新更新时间',
  view_count BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览/播放量',
  fav_count BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '收藏量',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  KEY idx_type_status_updated (content_type, publish_status, updated_at),
  KEY idx_status_view (publish_status, view_count),
  KEY idx_admin_id (admin_id),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='作品主表';

-- 2. 分季 / 分卷表
CREATE TABLE chapter_volumes (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '分季/分卷ID',
  work_id BIGINT UNSIGNED NOT NULL COMMENT '所属作品ID',
  sort_order INT NOT NULL DEFAULT 0 COMMENT '分卷排序(建议步长10)',
  title VARCHAR(200) NOT NULL COMMENT '分卷/分季标题',
  cover_path VARCHAR(255) NOT NULL DEFAULT '' COMMENT '分卷封面路径',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  KEY idx_work_sort (work_id, sort_order, id),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分季/分卷表';

-- 3. 章节 / 分集主表
CREATE TABLE chapters (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '章节/分集ID',
  admin_id BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建/操作管理员ID',
  work_id BIGINT UNSIGNED NOT NULL COMMENT '所属作品ID',
  volume_id BIGINT UNSIGNED NULL COMMENT '所属分季/分卷ID(无分卷时可为空)',
  sort_order INT NOT NULL DEFAULT 0 COMMENT '排序号(建议步长10)',
  title VARCHAR(200) NOT NULL COMMENT '章节/分集标题',
  access_type TINYINT NOT NULL DEFAULT 0 COMMENT '默认访问权限: 0免费 1单章付费 2VIP专属',
  default_price INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认单章/单集价格(书币/积分或分)',
  chapter_status TINYINT NOT NULL DEFAULT 0 COMMENT '状态: 0处理中/草稿 1已发布 2已下架',
  published_at DATETIME NULL COMMENT '发布时间',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  KEY idx_work_sort (work_id, sort_order, id),
  KEY idx_work_volume_sort (work_id, volume_id, sort_order, id),
  KEY idx_work_status (work_id, chapter_status),
  KEY idx_admin_id (admin_id),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='章节/分集主表';

-- 4. 视频分集扩展表(.ts分片不入库)
CREATE TABLE video_chapter_exts (
  chapter_id BIGINT UNSIGNED PRIMARY KEY COMMENT '对应 chapters.id',
  m3u8_path VARCHAR(255) NOT NULL DEFAULT '' COMMENT '当前m3u8路径(或版本目录路径)',
  poster_path VARCHAR(255) NOT NULL DEFAULT '' COMMENT '当前视频封面路径',
  duration_sec INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '视频时长(秒)',
  transcode_status TINYINT NOT NULL DEFAULT 0 COMMENT '转码状态: 0待处理 1转码中 2成功 3失败',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='视频分集扩展表';

-- 5. 漫画章节扩展表 & 单页明细表
CREATE TABLE comic_chapter_exts (
  chapter_id BIGINT UNSIGNED PRIMARY KEY COMMENT '对应 chapters.id',
  reading_mode VARCHAR(16) NOT NULL DEFAULT 'vertical' COMMENT '阅读方向: vertical条漫 / ltr从左到右 / rtl从右到左',
  page_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '本章总页数',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='漫画章节扩展表';

CREATE TABLE comic_chapter_pages (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '单页ID',
  chapter_id BIGINT UNSIGNED NOT NULL COMMENT '对应 chapters.id',
  page_no INT UNSIGNED NOT NULL COMMENT '页码(从1开始)',
  image_path VARCHAR(255) NOT NULL COMMENT '当前单页图片路径',
  mime_type VARCHAR(32) NOT NULL DEFAULT 'image/webp' COMMENT '图片MIME类型',
  width INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '图片宽度(px)',
  height INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '图片高度(px)',
  file_size_bytes INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '单页大小(字节)',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  UNIQUE KEY uk_chapter_page (chapter_id, page_no),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='漫画单页明细表';

-- 6. 小说章节扩展表
CREATE TABLE novel_chapter_exts (
  chapter_id BIGINT UNSIGNED PRIMARY KEY COMMENT '对应 chapters.id',
  word_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '本章字数',
  content_path VARCHAR(255) NOT NULL DEFAULT '' COMMENT '当前正文文件路径',
  content_text MEDIUMTEXT NULL COMMENT '正文内容(可选)',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='小说章节扩展表';

-- 7. 合集主表 & 合集明细表
CREATE TABLE collections (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '合集ID',
  admin_id BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建/操作管理员ID',
  content_type VARCHAR(16) NOT NULL DEFAULT 'mixed' COMMENT '合集类型: video / comic / novel / tv_series / short_drama / mixed',
  title VARCHAR(200) NOT NULL COMMENT '合集标题',
  cover_path VARCHAR(255) NOT NULL DEFAULT '' COMMENT '合集封面路径',
  summary VARCHAR(2000) NOT NULL DEFAULT '' COMMENT '合集简介',
  publish_status TINYINT NOT NULL DEFAULT 1 COMMENT '状态: 0草稿 1上架 2下架',
  published_at DATETIME NULL COMMENT '上架时间',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  KEY idx_type_status (content_type, publish_status),
  KEY idx_admin_id (admin_id),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='合集主表';

CREATE TABLE collection_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '合集条目ID',
  collection_id BIGINT UNSIGNED NOT NULL COMMENT '合集ID',
  item_type VARCHAR(16) NOT NULL COMMENT '条目类型: work / chapter',
  work_id BIGINT UNSIGNED NOT NULL COMMENT '关联作品ID',
  chapter_id BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '当item_type=chapter时填章节ID，整部作品时填0',
  sort_order INT NOT NULL DEFAULT 0 COMMENT '合集内排序(建议步长10)',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(非空表示已删除)',
  UNIQUE KEY uk_col_item (collection_id, item_type, work_id, chapter_id),
  KEY idx_col_sort (collection_id, sort_order, id),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='合集明细表';

-- 8. 独立待清理文件表(区分上传新文件替换 vs 资源逻辑删除)
CREATE TABLE file_cleanup_queue (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '清理任务ID',
  file_path VARCHAR(255) NOT NULL COMMENT '待清理文件路径(或视频版本目录路径)',
  file_kind VARCHAR(20) NOT NULL DEFAULT 'file' COMMENT '清理类型: file单文件 / dir目录(如m3u8版本目录)',
  cleanup_reason VARCHAR(20) NOT NULL COMMENT '入队原因: replaced替换新文件 / soft_deleted资源逻辑删除',
  admin_id BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '触发操作管理员ID',
  cleanup_status TINYINT NOT NULL DEFAULT 0 COMMENT '处理状态: 0待清理 1已物理删除 2清理失败 3已跳过/恢复取消',
  delete_after_at DATETIME NOT NULL COMMENT '允许执行物理删除的最早时间(保留期截止点)',
  cleaned_at DATETIME NULL DEFAULT NULL COMMENT '实际物理删除完成时间',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '入队时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  KEY idx_status_delete_after (cleanup_status, delete_after_at, id),
  KEY idx_reason_status (cleanup_reason, cleanup_status),
  KEY idx_file_path (file_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='独立待清理文件队列表';

-- 9. 后台操作日志表
CREATE TABLE admin_audit_logs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '日志ID',
  admin_id BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作管理员ID',
  client_ip VARCHAR(45) NOT NULL DEFAULT '' COMMENT '操作来源IP',
  detail_summary VARCHAR(2000) NOT NULL DEFAULT '' COMMENT '操作摘要或字段变更说明(纯文本)',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '操作时间',
  KEY idx_admin_created (admin_id, created_at),
  KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='后台操作日志表';

-- 10. 用户基础表
CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '用户ID',
  nickname VARCHAR(100) NOT NULL DEFAULT '' COMMENT '用户昵称',
  coin_balance INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '书币/积分余额',
  vip_expire_at DATETIME NULL COMMENT 'VIP到期时间(为空或早于当前时间表示非VIP)',
  account_status TINYINT NOT NULL DEFAULT 1 COMMENT '账号状态: 1正常 2封禁',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间',
  KEY idx_status (account_status),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- 11. 用户单章/单集解锁记录表
CREATE TABLE user_chapter_unlocks (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '解锁记录ID',
  user_id BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
  work_id BIGINT UNSIGNED NOT NULL COMMENT '作品ID',
  chapter_id BIGINT UNSIGNED NOT NULL COMMENT '章节/单视频ID',
  content_type VARCHAR(16) NOT NULL COMMENT '内容类型: video / comic / novel / tv_series / short_drama',
  unlock_source VARCHAR(20) NOT NULL DEFAULT 'coin' COMMENT '解锁来源: coin书币购买 / vip会员 / gift赠送 / promo活动',
  paid_amount INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '实际消耗书币/金额',
  order_no VARCHAR(64) NOT NULL DEFAULT '' COMMENT '关联订单号',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '解锁时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间',
  UNIQUE KEY uk_user_chapter (user_id, chapter_id),
  KEY idx_user_work (user_id, work_id),
  KEY idx_order_no (order_no),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户单章/单集解锁记录表';

-- 12. 用户收藏表(书架)
CREATE TABLE user_favorites (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '收藏ID',
  user_id BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
  work_id BIGINT UNSIGNED NOT NULL COMMENT '作品ID',
  content_type VARCHAR(16) NOT NULL COMMENT '内容类型: video / comic / novel / tv_series / short_drama',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '收藏时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间(取消收藏可填时间)',
  UNIQUE KEY uk_user_work (user_id, work_id),
  KEY idx_user_type_created (user_id, content_type, created_at),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户收藏表';

-- 13. 用户浏览/观看/阅读进度表
CREATE TABLE user_read_histories (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '历史记录ID',
  user_id BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
  work_id BIGINT UNSIGNED NOT NULL COMMENT '作品ID',
  chapter_id BIGINT UNSIGNED NOT NULL COMMENT '最后观看/阅读的章节ID',
  chapter_title VARCHAR(200) NOT NULL DEFAULT '' COMMENT '最后观看/阅读的章节标题',
  content_type VARCHAR(16) NOT NULL COMMENT '内容类型: video / comic / novel / tv_series / short_drama',
  progress_value INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '进度值',
  progress_unit VARCHAR(16) NOT NULL DEFAULT 'sec' COMMENT '进度单位: sec秒 / page页 / char字符 / pct百分比',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '首次记录时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间',
  UNIQUE KEY uk_user_work (user_id, work_id),
  KEY idx_user_updated (user_id, updated_at),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户浏览/阅读进度表';

-- 14. 基础订单表
CREATE TABLE orders (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT COMMENT '订单ID',
  order_no VARCHAR(64) NOT NULL COMMENT '业务订单号',
  user_id BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
  order_type VARCHAR(20) NOT NULL COMMENT '订单类型: recharge充值 / unlock_chapter单章解锁 / vip会员',
  target_id BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '目标ID(如 chapter_id 或套餐ID)',
  amount INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '金额/书币数量',
  pay_status TINYINT NOT NULL DEFAULT 0 COMMENT '支付状态: 0待支付 1已支付 2已取消 3已退款',
  paid_at DATETIME NULL COMMENT '支付时间',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  deleted_at DATETIME NULL DEFAULT NULL COMMENT '逻辑删除时间',
  UNIQUE KEY uk_order_no (order_no),
  KEY idx_user_created (user_id, created_at),
  KEY idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='基础订单表';

SET FOREIGN_KEY_CHECKS = 1;