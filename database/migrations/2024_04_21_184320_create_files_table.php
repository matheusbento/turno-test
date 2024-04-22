<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type');
            $table->integer('owner_id');
            $table->unsignedBigInteger('created_by_user_id')->nullable()->index();
            $table->string('file_type')->index();
            $table->string('drive');
            $table->string('url');
            $table->string('path');
            $table->string('original');
            $table->string('mime');
            $table->unsignedInteger('size');
            $table->integer('sort_order')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['owner_type', 'owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
