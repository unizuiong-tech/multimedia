<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_favorites', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('用户收藏表');
            }

            $table->bigIncrements('id')->comment('收藏ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('work_id')->comment('作品ID');
            $table->string('content_type', 16)->comment('内容类型: video / comic / novel / tv_series / short_drama');
            $table->dateTime('created_at')->useCurrent()->comment('收藏时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间(取消收藏可填时间)');
            $table->unique(['user_id', 'work_id'], 'user_favorites_uk_user_work');
            $table->index(['user_id', 'content_type', 'created_at'], 'idx_user_type_created');
            $table->index('deleted_at', 'user_favorites_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
    }
};
