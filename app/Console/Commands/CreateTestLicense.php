<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\License;
use App\Models\User;
use Illuminate\Support\Str;

class CreateTestLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:create {email} {plan=pro}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Create a test license key for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $plan = $this->argument('plan');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found with email: $email");
            return 1;
        }

        $licenseKey = 'VM-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));

        $license = License::create([
            'user_id' => $user->id,
            'license_key' => $licenseKey,
            'plan' => $plan,
            'status' => 'active',
            'expires_at' => now()->addYear(),
        ]);

        $this->info("License created successfully!");
        $this->line("Email: <info>$email</info>");
        $this->line("Key:   <comment>$licenseKey</comment>");
        $this->line("Plan:  <info>$plan</info>");

        return 0;
    }
}
