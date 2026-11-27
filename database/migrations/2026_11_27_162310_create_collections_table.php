<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('合集主表');
            }

            $table->bigIncrements('id')->comment('合集ID');
            $table->unsignedBigInteger('admin_id')->default(0)->comment('创建/操作管理员ID');
            $table->string('content_type', 16)->default('mixed')->comment('合集类型: video / comic / novel / tv_series / short_drama / mixed');
            $table->string('title', 200)->comment('合集标题');
            $table->string('cover_path', 255)->default('')->comment('合集封面路径');
            $table->string('summary', 2000)->default('')->comment('合集简介');
            $table->tinyInteger('publish_status')->default(1)->comment('状态: 0草稿 1上架 2下架');
            $table->dateTime('published_at')->nullable()->comment('上架时间');
            $table->dateTime('created_at')->useCurrent()->comment('创建时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间(非空表示已删除)');
            $table->index(['content_type', 'publish_status'], 'idx_type_status');
            $table->index('admin_id', 'collections_idx_admin_id');
            $table->index('deleted_at', 'collections_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
