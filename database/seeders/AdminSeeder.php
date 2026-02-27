<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database with super admin.
     */
    public function run(): void
    {
        $superAdminTelegramId = config('services.telegram.super_admin_id');

        if (empty($superAdminTelegramId)) {
            $this->command->error('SUPER_ADMIN_TELEGRAM_ID не установлен в .env файле!');
            return;
        }

        Admin::updateOrCreate(
            ['telegram_user_id' => $superAdminTelegramId],
            [
                'username' => null,
                'role' => AdminRole::SUPER_ADMIN,
                'is_active' => true,
            ]
        );

        $this->command->info("Супер-админ создан с Telegram ID: {$superAdminTelegramId}");
    }
}
