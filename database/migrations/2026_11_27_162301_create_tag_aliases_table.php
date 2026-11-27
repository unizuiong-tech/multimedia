<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_aliases', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('标签同义词映射表');
            }

            $table->bigIncrements('id')->comment('映射ID');
            $table->string('group_code', 32)->default('general')->comment('所属分组，与 tags.group_code 对齐');
            $table->string('alias_name', 50)->comment('同义词/别名，如: 沙雕 / 撒糖');
            $table->unsignedBigInteger('canonical_tag_id')->comment('归并到的标准主标签ID(对应 tags.id)');
            $table->dateTime('created_at')->useCurrent()->comment('创建时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间(非空表示已删除)');
            $table->unique(['group_code', 'alias_name'], 'uk_group_alias');
            $table->index('canonical_tag_id', 'idx_canonical_tag');
            $table->index('deleted_at', 'tag_aliases_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_aliases');
    }
};
