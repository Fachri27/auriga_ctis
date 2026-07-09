<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTest extends Command
{
    protected $signature = 'mail:test {user? : User ID or email address}';

    protected $description = 'Send a test email to a registered user';

    public function handle(): int
    {
        $target = $this->argument('user');

        if ($target) {
            $user = is_numeric($target)
                ? User::find((int) $target)
                : User::where('email', $target)->first();

            if (!$user) {
                $this->error("User not found: {$target}");
                return Command::FAILURE;
            }

            Mail::to($user)->send(new TestMail($user->name));
            $this->info("Test email sent to: {$user->name} <{$user->email}>");
            return Command::SUCCESS;
        }

        $users = User::all();
        if ($users->isEmpty()) {
            $this->error('No users found.');
            return Command::FAILURE;
        }

        foreach ($users as $user) {
            Mail::to($user)->send(new TestMail($user->name));
            $this->line("  Sent to: {$user->name} <{$user->email}>");
        }

        $this->info("Test email sent to {$users->count()} user(s).");
        return Command::SUCCESS;
    }
}
