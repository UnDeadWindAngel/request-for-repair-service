<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RepairRequest;
use App\Models\User;

class RequestsTableSeeder extends Seeder
{
    public function run(): void
    {
        $masters = User::where('role', 'master')->pluck('id')->toArray();

        if (empty($masters)) {
            $masters = [null];
        }

        // Определяем возможные статусы
        $statuses = ['new', 'assigned', 'in_progress', 'done', 'canceled'];

        for ($i = 1; $i <= 10; $i++) {
            $status = $statuses[array_rand($statuses)];
            $assignedTo = null;

            // Если статус требует назначения, выберем случайного мастера
            if (in_array($status, ['assigned', 'in_progress', 'done'])) {
                $assignedTo = $masters[array_rand($masters)];
                // Если мастеров нет, статус останется new
                if ($assignedTo === null) {
                    $status = 'new';
                }
            }

            RepairRequest::create([
                'clientName' => "Клиент $i",
                'phone' => '123456789' . $i,
                'address' => "Адрес $i, ул. Примерная, д. $i",
                'problemText' => "Описание проблемы для заявки $i",
                'status' => $status,
                'assignedTo' => $assignedTo,
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now(),
            ]);
        }
    }
}
