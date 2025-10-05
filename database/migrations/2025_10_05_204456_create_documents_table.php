<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('file_path')->comment('Path to file in private storage');
            $table->string('original_filename')->comment('User\'s original filename');
            $table->unsignedBigInteger('file_size')->nullable()->comment('File size in bytes');
            $table->string('mime_type')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('document_type_id');
            $table->index('branch_id');
            $table->index('uploaded_by');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
