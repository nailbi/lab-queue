<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Учётная запись старосты (админа).
        // ВХОД: starosta@example.com / password — поменяйте пароль!
        User::updateOrCreate(
            ['email' => 'starosta@example.com'],
            ['name' => 'Староста', 'password' => Hash::make('password')]
        );

        // Пара демонстрационных предметов.
        Subject::firstOrCreate(
            ['name' => 'Серверная веб-разработка'],
            ['description' => "Лабораторные по PHP и Laravel.\nЗащита по записи в очередь.", 'lab_count' => 5]
        );
        Subject::firstOrCreate(
            ['name' => 'Базы данных'],
            ['description' => 'Проектирование и работа с реляционными БД.', 'lab_count' => 4]
        );
    }
}
