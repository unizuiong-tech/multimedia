<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comic_chapter_exts', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('漫画章节扩展表');
            }

            $table->unsignedBigInteger('chapter_id')->primary()->comment('对应 chapters.id');
            $table->string('reading_mode', 16)->default('vertical')->comment('阅读方向: vertical条漫 / ltr从左到右 / rtl从右到左');
            $table->unsignedInteger('page_count')->default(0)->comment('本章总页数');
            $table->dateTime('created_at')->useCurrent()->comment('创建时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间(非空表示已删除)');
            $table->index('deleted_at', 'comic_chapter_exts_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comic_chapter_exts');
    }
};
