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
        if (Schema::hasTable('assignment_attachments')) {
            return; // Table already exists, skip migration
        }
        
        Schema::create('assignment_attachments', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship - can be attached to assignment or submission
            $table->morphs('attachable'); // attachable_type, attachable_id
            
            // File Information
            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_type')->nullable(); // pdf, image, doc, etc.
            $table->integer('file_size')->nullable(); // in bytes
            $table->string('mime_type')->nullable();
            
            // Metadata
            $table->string('description')->nullable();
            $table->integer('sort_order')->default(0);
            
            // Company & Branch
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            // Note: morphs() already creates index on attachable_type and attachable_id
            $table->index(['company_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_attachments');
    }
};
