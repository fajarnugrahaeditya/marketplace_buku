<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->upsert([
            ['key' => 'royalty_author_percent', 'value' => '90'],
            ['key' => 'min_price',              'value' => '10000'],
            ['key' => 'order_expiry_hours',     'value' => '24'],
            // contoh saja, ubah sesuai keputusanmu
            ['key' => 'min_withdrawal',         'value' => '50000'],
        ], ['key'], ['value']);
    }
}
