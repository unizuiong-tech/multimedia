<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('works', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('作品主表');
            }

            $table->bigIncrements('id')->comment('作品ID');
            $table->unsignedBigInteger('admin_id')->default(0)->comment('创建/操作管理员ID');
            $table->string('content_type', 16)->comment('内容类型: video / comic / novel / tv_series / short_drama');
            $table->string('title', 200)->comment('作品标题');
            $table->string('sub_title', 200)->default('')->comment('副标题/别名');
            $table->string('cover_path', 255)->default('')->comment('当前封面路径');
            $table->string('author', 100)->default('')->comment('作者/UP主/制作方');
            $table->string('summary', 2000)->default('')->comment('作品简介');
            $table->tinyInteger('publish_status')->default(0)->comment('发布状态: 0草稿 1连载中 2已完结 3已下架');
            $table->unsignedInteger('chapter_count')->default(0)->comment('已发布章节/分集总数');
            $table->unsignedBigInteger('latest_chapter_id')->nullable()->comment('最新章节/分集ID');
            $table->string('latest_chapter_title', 200)->default('')->comment('最新章节/分集标题');
            $table->dateTime('latest_chapter_at')->nullable()->comment('最新更新时间');
            $table->unsignedBigInteger('view_count')->default(0)->comment('浏览/播放量');
            $table->unsignedBigInteger('fav_count')->default(0)->comment('收藏量');
            $table->dateTime('created_at')->useCurrent()->comment('创建时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间(非空表示已删除)');
            $table->index(['content_type', 'publish_status', 'updated_at'], 'idx_type_status_updated');
            $table->index(['publish_status', 'view_count'], 'idx_status_view');
            $table->index('admin_id', 'works_idx_admin_id');
            $table->index('deleted_at', 'works_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
