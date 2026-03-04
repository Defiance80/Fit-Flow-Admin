<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class RefundSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $refundSettings = [
            [
                'name' => 'refund_enabled',
                'value' => '0',
                'type' => 'boolean'
            ],
            [
                'name' => 'refund_period_days',
                'value' => '7',
                'type' => 'number'
            ]
        ];

        foreach ($refundSettings as $setting) {
            Setting::updateOrCreate(
                ['name' => $setting['name']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }
    }
}
