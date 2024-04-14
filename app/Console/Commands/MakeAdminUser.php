<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:make-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo "Creating admin user...\n";
        User::updateOrCreate([
            'email' => 'steppershop@gmail.com',
        ], [
            'name' => 'Admin',
            'password' => bcrypt('_/5steppershop-1p2a3ss8word'),
        ]);

    }
}
