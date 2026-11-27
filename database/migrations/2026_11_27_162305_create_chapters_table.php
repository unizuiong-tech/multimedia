<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('章节/分集主表');
            }

            $table->bigIncrements('id')->comment('章节/分集ID');
            $table->unsignedBigInteger('admin_id')->default(0)->comment('创建/操作管理员ID');
            $table->unsignedBigInteger('work_id')->comment('所属作品ID');
            $table->unsignedBigInteger('volume_id')->nullable()->comment('所属分季/分卷ID(无分卷时可为空)');
            $table->integer('sort_order')->default(0)->comment('排序号(建议步长10)');
            $table->string('title', 200)->comment('章节/分集标题');
            $table->tinyInteger('access_type')->default(0)->comment('默认访问权限: 0免费 1单章付费 2VIP专属');
            $table->unsignedInteger('default_price')->default(0)->comment('默认单章/单集价格(书币/积分或分)');
            $table->tinyInteger('chapter_status')->default(0)->comment('状态: 0处理中/草稿 1已发布 2已下架');
            $table->dateTime('published_at')->nullable()->comment('发布时间');
            $table->dateTime('created_at')->useCurrent()->comment('创建时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间(非空表示已删除)');
            $table->index(['work_id', 'sort_order', 'id'], 'chapters_idx_work_sort');
            $table->index(['work_id', 'volume_id', 'sort_order', 'id'], 'idx_work_volume_sort');
            $table->index(['work_id', 'chapter_status'], 'idx_work_status');
            $table->index('admin_id', 'chapters_idx_admin_id');
            $table->index('deleted_at', 'chapters_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
