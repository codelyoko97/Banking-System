<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          DB::table('types')->insertOrIgnore([
            ['id'=>1,'name'=>'savings','created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'name'=>'checking','created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'name'=>'loan','created_at'=>now(),'updated_at'=>now()],
            ['id'=>4,'name'=>'investment','created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
