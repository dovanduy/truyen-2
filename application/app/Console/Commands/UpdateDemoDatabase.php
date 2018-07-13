<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateDemoDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:updatedatabase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Database in every 6 hours';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            $dbHost=env('DB_HOST');
            $dbUsername=env('DB_USERNAME');
            $dbPassword=env('DB_PASSWORD');
            $dbName=env('DB_DATABASE');
            $dbh = new \PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
            $dbh->query("DROP DATABASE $dbName");
            $dbh->query('CREATE DATABASE IF NOT EXISTS '.$dbName.' CHARACTER SET utf8 COLLATE utf8_general_ci;');
            $dbh->query('USE DATABASE'.$dbName);
            $sql = file_get_contents(base_path('install/database/ultimate-sms.sql'));
            //$dbh->exec($sql);
        }
        catch(\Exception $e){
            return false;
        }
    }
}
