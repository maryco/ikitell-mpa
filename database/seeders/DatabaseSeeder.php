<?php

namespace Database\Seeders;

use Database\Seeders\Develop\DevContactSeeder;
use Database\Seeders\Develop\DevDeviceContactSeeder;
use Database\Seeders\Develop\DevDeviceSeeder;
use Database\Seeders\Develop\DevRuleSeeder;
use Database\Seeders\Develop\DevUserSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        switch (App::environment()) {
            case 'local':
                $this->runDevelopSeeder();
                break;
            case 'testing':
                $this->runTestSeeder();
                break;
            case 'production':
                // TODO: Run initial data seeder for the production.
                break;
            default:
                Log::warning('Nothing to do.');
                break;
        }
    }

    /**
     * Run seeder for the Develop database.
     */
    protected function runDevelopSeeder()
    {
        Log::info('Seeding for the Develop.');

        $this->call(DevUserSeeder::class);
        $this->call(DevDeviceSeeder::class);
        $this->call(DevRuleSeeder::class);
        $this->call(DevContactSeeder::class);
        $this->call(DevDeviceContactSeeder::class);
    }

    /**
     * Run seeder for the Develop database.
     */
    protected function runTestSeeder()
    {
        Log::info('Seeding for the Test.');

        $this->call(DevUserSeeder::class);
    }

}
