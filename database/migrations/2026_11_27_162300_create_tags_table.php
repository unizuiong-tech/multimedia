<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('标签字典表');
            }

            $table->bigIncrements('id')->comment('标签ID');
            $table->string('group_code', 32)->default('general')->comment('标签分组: general通用 / genre题材 / mood看点 / region地区');
            $table->string('name', 50)->comment('标准标签名称，如: 悬疑 / 甜宠 / 搞笑 / 完结');
            $table->integer('sort_order')->default(0)->comment('同组内展示排序(步长10)');
            $table->dateTime('created_at')->useCurrent()->comment('创建时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间(非空表示已删除)');
            $table->unique(['group_code', 'name'], 'uk_group_name');
            $table->index(['group_code', 'sort_order', 'id'], 'idx_group_sort');
            $table->index('deleted_at', 'tags_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
