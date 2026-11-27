<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_items', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table->comment('合集明细表');
            }

            $table->bigIncrements('id')->comment('合集条目ID');
            $table->unsignedBigInteger('collection_id')->comment('合集ID');
            $table->string('item_type', 16)->comment('条目类型: work / chapter');
            $table->unsignedBigInteger('work_id')->comment('关联作品ID');
            $table->unsignedBigInteger('chapter_id')->default(0)->comment('当item_type=chapter时填章节ID，整部作品时填0');
            $table->integer('sort_order')->default(0)->comment('合集内排序(建议步长10)');
            $table->dateTime('created_at')->useCurrent()->comment('添加时间');
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->dateTime('deleted_at')->nullable()->default(null)->comment('逻辑删除时间(非空表示已删除)');
            $table->unique(['collection_id', 'item_type', 'work_id', 'chapter_id'], 'uk_col_item');
            $table->index(['collection_id', 'sort_order', 'id'], 'idx_col_sort');
            $table->index('deleted_at', 'collection_items_idx_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_items');
    }
};
