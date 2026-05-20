<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarSharingGroup;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $demo = User::updateOrCreate(
            ['email' => 'joachim.vanmeirvenne@gmail.com'],
            ['name' => 'Joachim Van Meirvenne']
        );

        $neighbours = User::updateOrCreate(
            ['email' => 'sofie@example.com'],
            ['name' => 'Sofie Janssens']
        );

        $colleague = User::updateOrCreate(
            ['email' => 'pieter@example.com'],
            ['name' => 'Pieter De Smet']
        );

        $groupGent = CarSharingGroup::updateOrCreate(
            ['name' => 'Buurtdeel Gent-Brugse Poort'],
            ['city' => 'Gent', 'description' => 'Drie wagens, twaalf gezinnen.']
        );

        $groupLeuven = CarSharingGroup::updateOrCreate(
            ['name' => 'Cohousing De Klimop'],
            ['city' => 'Leuven', 'description' => 'Cohousing-project met één elektrische deelauto.']
        );

        $groupGent->users()->syncWithoutDetaching([$demo->id, $neighbours->id, $colleague->id]);
        $groupLeuven->users()->syncWithoutDetaching([$demo->id, $neighbours->id]);

        $renault = Car::updateOrCreate(
            ['car_sharing_group_id' => $groupGent->id, 'name' => 'Zoé'],
            ['brand' => 'Renault', 'model' => 'Zoe', 'license_plate' => '1-ABC-123', 'color' => '#4f46e5']
        );

        $citroen = Car::updateOrCreate(
            ['car_sharing_group_id' => $groupGent->id, 'name' => 'Cactus'],
            ['brand' => 'Citroën', 'model' => 'C4 Cactus', 'license_plate' => '1-XYZ-789', 'color' => '#0ea5e9']
        );

        $bestel = Car::updateOrCreate(
            ['car_sharing_group_id' => $groupGent->id, 'name' => 'Bestelwagen'],
            ['brand' => 'Peugeot', 'model' => 'Partner', 'license_plate' => '1-PQR-456', 'color' => '#f59e0b']
        );

        $klimop = Car::updateOrCreate(
            ['car_sharing_group_id' => $groupLeuven->id, 'name' => 'Bluebird'],
            ['brand' => 'Volkswagen', 'model' => 'ID.3', 'license_plate' => '1-KLO-321', 'color' => '#10b981']
        );

        $today = now()->startOfDay();

        Reservation::firstOrCreate(
            ['car_id' => $renault->id, 'user_id' => $demo->id, 'starts_at' => $today->copy()->addDays(2)->setTime(9, 0)],
            ['ends_at' => $today->copy()->addDays(2)->setTime(17, 0), 'purpose' => 'Familiebezoek']
        );

        Reservation::firstOrCreate(
            ['car_id' => $renault->id, 'user_id' => $neighbours->id, 'starts_at' => $today->copy()->addDays(5)->setTime(8, 0)],
            ['ends_at' => $today->copy()->addDays(5)->setTime(12, 0), 'purpose' => 'Boodschappen']
        );

        Reservation::firstOrCreate(
            ['car_id' => $bestel->id, 'user_id' => $colleague->id, 'starts_at' => $today->copy()->addDays(1)->setTime(10, 0)],
            ['ends_at' => $today->copy()->addDays(1)->setTime(18, 0), 'purpose' => 'Verhuis']
        );

        Reservation::firstOrCreate(
            ['car_id' => $klimop->id, 'user_id' => $demo->id, 'starts_at' => $today->copy()->addDays(7)->setTime(14, 0)],
            ['ends_at' => $today->copy()->addDays(7)->setTime(20, 0), 'purpose' => 'Vergadering']
        );
    }
}
