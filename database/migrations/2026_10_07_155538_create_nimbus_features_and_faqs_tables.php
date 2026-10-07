<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nimbus_features', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('cpu');
            $table->string('tag')->nullable();
            $table->string('title');
            $table->text('copy');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nimbus_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed initial Architecture features
        $now = now();
        DB::table('nimbus_features')->insert([
            [
                'icon' => 'cpu',
                'tag' => 'PERFORMANCE',
                'title' => 'Lightweight Core (<25MB RAM)',
                'copy' => 'Unlike legacy control panels that consume gigabytes of system memory, Nimbus runs lean with near-zero daemon overhead.',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'icon' => 'terminal',
                'tag' => 'CONTAINERS',
                'title' => 'Docker & Compose Native',
                'copy' => 'Deploy and manage containerized applications, multi-service Docker Compose files, and automated volume mounts with 1 click.',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'icon' => 'shield',
                'tag' => 'SECURITY',
                'title' => 'Automated SSL & Firewalls',
                'copy' => 'Automatic Let’s Encrypt certificate issuance and renewal, managed UFW port rules, and integrated fail2ban intrusion prevention.',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'icon' => 'zap',
                'tag' => 'WEB SERVER',
                'title' => 'Nginx, HTTP/3 & Brotli',
                'copy' => 'Pre-tuned Nginx reverse proxy configuration supporting HTTP/3, Brotli/Gzip compression, WebSockets, and custom upstream rules.',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'icon' => 'database',
                'tag' => 'DATABASES',
                'title' => 'MySQL, Postgres & Redis',
                'copy' => 'One-click database provisioning, user privilege boundaries, automated local snapshots, and scheduled off-site S3 uploads.',
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'icon' => 'workflow',
                'tag' => 'CI/CD',
                'title' => 'Git Push-to-Deploy',
                'copy' => 'Connect GitHub, GitLab, or Bitbucket webhooks for zero-downtime automated deployment on every push to your production branch.',
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Seed initial FAQs
        DB::table('nimbus_faqs')->insert([
            [
                'question' => 'What Linux distributions are supported?',
                'answer' => 'Nimbus is officially tested and optimized for clean installations of Ubuntu 20.04, 22.04, and 24.04 LTS, as well as Debian 11 (Bullseye) and Debian 12 (Bookworm).',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question' => 'How does licensing and activation work?',
                'answer' => 'After purchasing or generating your license key in your client workspace, simply run `nimbus activate <YOUR-LICENSE-KEY>` on your server terminal. The panel will immediately unlock your tier capabilities.',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question' => 'Can I migrate my existing sites to Nimbus?',
                'answer' => 'Yes. Nimbus includes automated file, database, and Nginx vhost import utilities to help you smoothly migrate existing websites from standard LAMP/LEMP stacks, cPanel, or Plesk.',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question' => 'What is the difference between Nimbus and Managed Hosting?',
                'answer' => 'Nimbus is our standalone software product for developers who want to manage their own VPS or bare-metal server. If you prefer our engineering team to handle the servers, updates, backups, and 24/7 reliability for you, our Managed Hosting service is the ideal choice.',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question' => 'Where are automated backups stored?',
                'answer' => 'Backups can be stored locally on your server or automatically streamed to external S3-compatible cloud storage (AWS S3, Cloudflare R2, Backblaze B2, or MinIO).',
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nimbus_faqs');
        Schema::dropIfExists('nimbus_features');
    }
};
