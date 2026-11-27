<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_read_histories', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('用户浏览/阅读进度表');
            }

            $table->bigIncrements('id')->comment('历史记录ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('work_id')->comment('作品ID');
            $table->unsignedBigInteger('chapter_id')->comment('最后观看/阅读的章节ID');
            $table->string('chapter_title', 200)->default('')->comment('最后观看/阅读的章节标题');
            $table->string('content_type', 16)->comment('内容类型: video / comic / novel / tv_series / short_drama');
            $table->unsignedInteger('progress_value')->default(0)->comment('进度值');
            $table->string('progress_unit', 16)->default('sec')->comment('进度单位: sec秒 / page页 / char字符 / pct百分比');
            $table->dateTime('created_at')->useCurrent()->comment('首次记录时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('最后更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间');
            $table->unique(['user_id', 'work_id'], 'user_read_histories_uk_user_work');
            $table->index(['user_id', 'updated_at'], 'idx_user_updated');
            $table->index('deleted_at', 'user_read_histories_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_read_histories');
    }
};
