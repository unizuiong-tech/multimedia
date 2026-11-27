<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_tags', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('作品标签关联表');
            }

            $table->bigIncrements('id')->comment('关联ID');
            $table->unsignedBigInteger('work_id')->comment('作品ID(对应 works.id)');
            $table->unsignedBigInteger('tag_id')->comment('标准标签ID(对应 tags.id)');
            $table->dateTime('created_at')->useCurrent()->comment('关联时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间(非空表示已删除)');
            $table->unique(['work_id', 'tag_id'], 'uk_work_tag');
            $table->index(['tag_id', 'work_id'], 'idx_tag_work');
            $table->index('deleted_at', 'work_tags_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_tags');
    }
};
