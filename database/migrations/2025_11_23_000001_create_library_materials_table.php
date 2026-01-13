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
        Schema::create('library_materials', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('title');
            $table->enum('type', ['pdf_book', 'notes', 'past_paper', 'assignment'])->default('pdf_book');
            $table->text('description')->nullable();
            
            // File Information
            $table->string('file_path');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_size')->nullable(); // in bytes
            $table->string('mime_type')->nullable();
            
            // Academic Setup
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            
            // Status
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_active')->default(true);
            
            // Company & Branch
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            
            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'branch_id', 'is_active']);
            $table->index(['academic_year_id', 'class_id', 'subject_id']);
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_materials');
    }
};

