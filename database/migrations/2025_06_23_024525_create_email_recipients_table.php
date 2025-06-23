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
        Schema::create('email_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('email_sequence_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'scheduled_for']);
            $table->unique(
                ['email_campaign_id', 'email_sequence_id', 'user_id'],
                'uniq_email_campaign_sequence_user'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_recipients');
    }
};
