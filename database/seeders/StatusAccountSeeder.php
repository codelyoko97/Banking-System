<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('statuses')->insertOrIgnore([
            ['id'=>1,'name'=>'active','created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'name'=>'frozen','created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'name'=>'suspended','created_at'=>now(),'updated_at'=>now()],
            ['id'=>4,'name'=>'closed','created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
