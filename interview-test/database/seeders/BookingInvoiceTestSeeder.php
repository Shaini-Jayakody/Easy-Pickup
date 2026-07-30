<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CarDetail\Car;
use App\Models\CarDetail\CarBrand;
use App\Models\CarDetail\CarModel;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class BookingInvoiceTestSeeder extends Seeder
{
    public function run()
    {
        // ============================================
        // 1. CREATE BRANDS AND MODELS (if not exist)
        // ============================================
        $brands = [
            'Toyota' => ['Camry', 'Corolla', 'Prius', 'Highlander'],
            'Honda' => ['Civic', 'Accord', 'CR-V', 'Pilot'],
            'BMW' => ['3 Series', '5 Series', 'X5', 'X3'],
            'Mercedes' => ['C-Class', 'E-Class', 'S-Class', 'GLC'],
            'Audi' => ['A4', 'A6', 'Q5', 'Q7'],
        ];

        foreach ($brands as $brandName => $models) {
            $brand = CarBrand::firstOrCreate(['name' => $brandName]);
            foreach ($models as $modelName) {
                CarModel::firstOrCreate([
                    'name' => $modelName,
                    'brand_id' => $brand->id
                ]);
            }
        }

        // ============================================
        // 2. CREATE CARS
        // ============================================
        $carsData = [
            [
                'name' => 'Toyota Camry',
                'model_id' => CarModel::where('name', 'Camry')->first()->id,
                'color' => 'Silver',
                'engine_number' => 'ENG' . rand(10000, 99999),
                'chassis_number' => 'CHS' . rand(10000, 99999),
                'number_plate' => 'ABC-' . rand(1000, 9999),
                'transmition' => 'Auto',
                'rent_price_per_hour' => 2500,
                'ref_no' => 'CAR' . date('ymd') . rand(1000, 9999),
            ],
            [
                'name' => 'Honda Civic',
                'model_id' => CarModel::where('name', 'Civic')->first()->id,
                'color' => 'White',
                'engine_number' => 'ENG' . rand(10000, 99999),
                'chassis_number' => 'CHS' . rand(10000, 99999),
                'number_plate' => 'XYZ-' . rand(1000, 9999),
                'transmition' => 'Manual',
                'rent_price_per_hour' => 2000,
                'ref_no' => 'CAR' . date('ymd') . rand(1000, 9999),
            ],
            [
                'name' => 'BMW 3 Series',
                'model_id' => CarModel::where('name', '3 Series')->first()->id,
                'color' => 'Black',
                'engine_number' => 'ENG' . rand(10000, 99999),
                'chassis_number' => 'CHS' . rand(10000, 99999),
                'number_plate' => 'DEF-' . rand(1000, 9999),
                'transmition' => 'Auto',
                'rent_price_per_hour' => 4000,
                'ref_no' => 'CAR' . date('ymd') . rand(1000, 9999),
            ],
            [
                'name' => 'Mercedes C-Class',
                'model_id' => CarModel::where('name', 'C-Class')->first()->id,
                'color' => 'Blue',
                'engine_number' => 'ENG' . rand(10000, 99999),
                'chassis_number' => 'CHS' . rand(10000, 99999),
                'number_plate' => 'GHI-' . rand(1000, 9999),
                'transmition' => 'Auto',
                'rent_price_per_hour' => 4500,
                'ref_no' => 'CAR' . date('ymd') . rand(1000, 9999),
            ],
            [
                'name' => 'Audi A4',
                'model_id' => CarModel::where('name', 'A4')->first()->id,
                'color' => 'Red',
                'engine_number' => 'ENG' . rand(10000, 99999),
                'chassis_number' => 'CHS' . rand(10000, 99999),
                'number_plate' => 'JKL-' . rand(1000, 9999),
                'transmition' => 'Auto',
                'rent_price_per_hour' => 3500,
                'ref_no' => 'CAR' . date('ymd') . rand(1000, 9999),
            ],
        ];

        foreach ($carsData as $carData) {
            Car::firstOrCreate(
                ['number_plate' => $carData['number_plate']],
                $carData
            );
        }

        // Get all cars
        $cars = Car::all();

        // ============================================
        // 3. CREATE USERS WITH MULTIPLE BOOKINGS
        // ============================================

        // --- User 1: John Doe (10+ bookings - 20% discount) ---
        $john = User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'id_num' => 'ID001',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
                'age' => 30,
                'gender' => 'Male',
                'address' => '123 Main St, City',
                'role' => 'user'
            ]
        );

        // --- User 2: Jane Smith (5+ bookings - 10% discount) ---
        $jane = User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Smith',
                'id_num' => 'ID002',
                'email' => 'jane@example.com',
                'password' => Hash::make('password123'),
                'age' => 28,
                'gender' => 'Female',
                'address' => '456 Oak Ave, Town',
                'role' => 'user'
            ]
        );

        // --- User 3: Bob Wilson (3+ bookings - 5% discount) ---
        $bob = User::firstOrCreate(
            ['email' => 'bob@example.com'],
            [
                'name' => 'Bob Wilson',
                'id_num' => 'ID003',
                'email' => 'bob@example.com',
                'password' => Hash::make('password123'),
                'age' => 35,
                'gender' => 'Male',
                'address' => '789 Pine Rd, Village',
                'role' => 'user'
            ]
        );

        // --- User 4: Alice Brown (New user - no discount) ---
        $alice = User::firstOrCreate(
            ['email' => 'alice@example.com'],
            [
                'name' => 'Alice Brown',
                'id_num' => 'ID004',
                'email' => 'alice@example.com',
                'password' => Hash::make('password123'),
                'age' => 25,
                'gender' => 'Female',
                'address' => '321 Elm St, County',
                'role' => 'user'
            ]
        );

        // --- User 5: Admin User ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'id_num' => 'ADMIN001',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'age' => 40,
                'gender' => 'Male',
                'address' => '999 Admin Blvd, City',
                'role' => 'admin'
            ]
        );

        // ============================================
        // 4. CREATE BOOKINGS FOR EACH USER
        // ============================================

        $this->command->info('Creating bookings for users...');

        // --- John Doe: 12 bookings ---
        $this->createBookingsForUser($john, $cars, 12, 'completed');

        // --- Jane Smith: 7 bookings ---
        $this->createBookingsForUser($jane, $cars, 7, 'completed');

        // --- Bob Wilson: 4 bookings  ---
        $this->createBookingsForUser($bob, $cars, 4, 'completed');

        // --- Alice Brown: 2 bookings  ---
        $this->createBookingsForUser($alice, $cars, 2, 'completed');

        // --- Also create some pending bookings ---
        $this->createBookingsForUser($john, $cars, 2, 'pending');
        $this->createBookingsForUser($jane, $cars, 1, 'pending');

        // --- Create some active bookings ---
        $this->createBookingsForUser($bob, $cars, 1, 'active');

        $this->command->info('Seeding completed successfully!');
        $this->command->info('Summary:');
        $this->command->info('   - John Doe: 12 completed bookings');
        $this->command->info('   - Jane Smith: 7 completed bookings');
        $this->command->info('   - Bob Wilson: 4 completed bookings');
        $this->command->info('   - Alice Brown: 2 completed bookings ');
        $this->command->info('   - Admin User: 0 completed bookings');
    }

    /**
     * Create bookings for a specific user
     */
    private function createBookingsForUser($user, $cars, $count, $status = 'completed')
    {
        $carCount = $cars->count();
        
        for ($i = 0; $i < $count; $i++) {
            // Pick a random car
            $car = $cars->random();
            
            // Generate random dates
            $daysAgo = rand(1, 60);
            $durationHours = rand(4, 48);
            
            $startDate = Carbon::now()->subDays($daysAgo)->setHour(rand(8, 20))->setMinute(0);
            $endDate = $startDate->copy()->addHours($durationHours);
            
            // Create booking
            Booking::create([
                'booking_ref_no' => 'BK' . date('ymd') . rand(1000, 9999),
                'user_id' => $user->user_id,
                'car_id' => $car->id,
                'rental_start_date' => $startDate,
                'rental_end_date' => $endDate,
                'status' => $status,
                'notes' => 'Test booking #' . ($i + 1) . ' for ' . $user->name,
            ]);
        }
    }
}