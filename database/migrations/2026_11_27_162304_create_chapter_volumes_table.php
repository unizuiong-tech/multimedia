<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapter_volumes', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('分季/分卷表');
            }

            $table->bigIncrements('id')->comment('分季/分卷ID');
            $table->unsignedBigInteger('work_id')->comment('所属作品ID');
            $table->integer('sort_order')->default(0)->comment('分卷排序(建议步长10)');
            $table->string('title', 200)->comment('分卷/分季标题');
            $table->string('cover_path', 255)->default('')->comment('分卷封面路径');
            $table->dateTime('created_at')->useCurrent()->comment('创建时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间(非空表示已删除)');
            $table->index(['work_id', 'sort_order', 'id'], 'chapter_volumes_idx_work_sort');
            $table->index('deleted_at', 'chapter_volumes_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_volumes');
    }
};
