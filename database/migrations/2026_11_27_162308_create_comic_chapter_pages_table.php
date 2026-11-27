<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comic_chapter_pages', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('漫画单页明细表');
            }

            $table->bigIncrements('id')->comment('单页ID');
            $table->unsignedBigInteger('chapter_id')->comment('对应 chapters.id');
            $table->unsignedInteger('page_no')->comment('页码(从1开始)');
            $table->string('image_path', 255)->comment('当前单页图片路径');
            $table->string('mime_type', 32)->default('image/webp')->comment('图片MIME类型');
            $table->unsignedInteger('width')->default(0)->comment('图片宽度(px)');
            $table->unsignedInteger('height')->default(0)->comment('图片高度(px)');
            $table->unsignedInteger('file_size_bytes')->default(0)->comment('单页大小(字节)');
            $table->dateTime('created_at')->useCurrent()->comment('创建时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间(非空表示已删除)');
            $table->unique(['chapter_id', 'page_no'], 'uk_chapter_page');
            $table->index('deleted_at', 'comic_chapter_pages_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comic_chapter_pages');
    }
};
