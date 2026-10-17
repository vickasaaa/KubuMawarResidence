<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Booking;
use App\Models\User;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear old rooms & bookings for clean seed
        Booking::query()->delete();
        Room::query()->delete();

        // 1. KUBU MAWAR RESIDENCE (Hanya 2 tipe kamar: Exclusive & Mediteranian)
        $residenceRooms = [
            [
                'property_type' => 'residence',
                'property_name' => 'Kubu Mawar Residence',
                'property_category' => 'homestay',
                'room_number' => 'KM-EX01',
                'name' => 'Exclusive Suite',
                'type_label' => 'Tipe Exclusive',
                'description' => 'Suite mewah dengan interior premium, king bed canopy, teras pribadi menghadap taman tropis, dan layanan eksklusif. Mendukung sewa mingguan & bulanan.',
                'image' => 'https://images.unsplash.com/photo-1590490360182-c33d955e4c47?auto=format&fit=crop&w=1200&q=80',
                'price_weekly' => 3200000,
                'price_monthly' => 11500000,
                'price_daily' => 500000,
                'allowed_rental_types' => 'weekly,monthly',
                'features' => ['King Bed Canopy', 'Teras Taman Pribadi', 'Living Room', 'Smart TV 55"', 'AC & Water Heater', 'WiFi High-Speed', 'Butler Service', 'Mini Bar Gratis'],
                'max_guests' => 2,
                'size_sqm' => 50,
                'floor' => 'Lantai 1',
                'status' => 'available', // Kosong
            ],
            [
                'property_type' => 'residence',
                'property_name' => 'Kubu Mawar Residence',
                'property_category' => 'homestay',
                'room_number' => 'KM-MD01',
                'name' => 'Mediteranian Suite',
                'type_label' => 'Tipe Mediteranian',
                'description' => 'Desain mediterania klasik dengan aksen batu alam, rotan, dan pencahayaan hangat. Nyaman, estetik, dengan balkon asri.',
                'image' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=80',
                'price_weekly' => 1800000,
                'price_monthly' => 6500000,
                'price_daily' => 300000,
                'allowed_rental_types' => 'weekly,monthly',
                'features' => ['Queen Bed Nyaman', 'Balkon Asri', 'Meja Kerja', 'AC & Water Heater', 'Smart TV 42"', 'WiFi Cepat', 'Kulkas Mini', 'Housekeeping'],
                'max_guests' => 2,
                'size_sqm' => 36,
                'floor' => 'Lantai 2',
                'status' => 'available', // Kosong
            ],
        ];

        // 2. KUBU MAWAR SUWUNG (10 unit kost, tipe sama: Kost Standard AC, sebagian terisi & sebagian kosong)
        $kostImage = 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1200&q=80';
        $kostFeatures = ['AC 1 PK', 'Kamar Mandi Dalam', 'Kasur Springbed', 'WiFi Fiber 50Mbps', 'Lemari 2 Pintu', 'Dapur Bersama', 'Parkir Motor', 'CCTV 24 Jam'];

        $suwungRooms = [];
        $occupiedIndexes = [2, 4, 7, 9]; // Unit 2, 4, 7, 9 terisi (4 terisi, 6 kosong)

        for ($i = 1; $i <= 10; $i++) {
            $isOccupied = in_array($i, $occupiedIndexes);
            $suwungRooms[] = [
                'property_type' => 'suwung',
                'property_name' => 'Kubu Mawar Suwung',
                'property_category' => 'kost',
                'room_number' => 'KM-SW' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'name' => 'Kamar Kost No. ' . $i,
                'type_label' => 'Kost Standard AC',
                'description' => 'Kamar kost modern nyaman ber-AC di Suwung Denpasar Selatan, kamar mandi dalam dengan shower air hangat, lemari dan meja kerja.',
                'image' => $kostImage,
                'price_weekly' => null, // Suwung hanya bulanan
                'price_monthly' => 1600000,
                'price_daily' => null,
                'allowed_rental_types' => 'monthly',
                'features' => $kostFeatures,
                'max_guests' => 1,
                'size_sqm' => 18,
                'floor' => $i <= 5 ? 'Lantai 1' : 'Lantai 2',
                'status' => $isOccupied ? 'occupied' : 'available',
            ];
        }

        // Insert Residence Rooms
        foreach ($residenceRooms as $data) {
            Room::create($data);
        }

        // Insert Suwung Rooms
        foreach ($suwungRooms as $data) {
            Room::create($data);
        }

        // Get test user
        $testUser = User::where('email', 'user@kubumawar.com')->first() ?: User::first();
        $userId = $testUser ? $testUser->id : null;

        // Create Seed Bookings for Occupied Rooms
        $sw02 = Room::where('room_number', 'KM-SW02')->first();
        if ($sw02) {
            Booking::create([
                'booking_code' => 'BK-SWG-' . date('Ymd') . '-002',
                'user_id' => $userId,
                'room_id' => $sw02->id,
                'guest_name' => 'Budi Pratama',
                'guest_email' => 'budi.p@example.com',
                'guest_phone' => '+62 819 8877 6655',
                'property_type' => 'suwung',
                'rental_type' => 'monthly',
                'duration' => 3,
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addDays(80)->toDateString(),
                'total_price' => 4800000,
                'status' => 'active',
                'notes' => 'Pekerja IT di Renon Denpasar.',
            ]);
        }

        $sw04 = Room::where('room_number', 'KM-SW04')->first();
        if ($sw04) {
            Booking::create([
                'booking_code' => 'BK-SWG-' . date('Ymd') . '-004',
                'user_id' => null,
                'room_id' => $sw04->id,
                'guest_name' => 'Rina Wijaya',
                'guest_email' => 'rina.w@example.com',
                'guest_phone' => '+62 821 4455 6677',
                'property_type' => 'suwung',
                'rental_type' => 'monthly',
                'duration' => 1,
                'start_date' => now()->subDays(5)->toDateString(),
                'end_date' => now()->addDays(25)->toDateString(),
                'total_price' => 1600000,
                'status' => 'active',
                'notes' => 'Bawa motor Honda Vario.',
            ]);
        }

        $sw07 = Room::where('room_number', 'KM-SW07')->first();
        if ($sw07) {
            Booking::create([
                'booking_code' => 'BK-SWG-' . date('Ymd') . '-007',
                'user_id' => null,
                'room_id' => $sw07->id,
                'guest_name' => 'Dewi Anggraini',
                'guest_email' => 'dewi.a@example.com',
                'guest_phone' => '+62 813 9988 1122',
                'property_type' => 'suwung',
                'rental_type' => 'monthly',
                'duration' => 2,
                'start_date' => now()->subDays(15)->toDateString(),
                'end_date' => now()->addDays(45)->toDateString(),
                'total_price' => 3200000,
                'status' => 'active',
                'notes' => 'Mahasiswi Kedokteran.',
            ]);
        }

        $sw09 = Room::where('room_number', 'KM-SW09')->first();
        if ($sw09) {
            Booking::create([
                'booking_code' => 'BK-SWG-' . date('Ymd') . '-009',
                'user_id' => null,
                'room_id' => $sw09->id,
                'guest_name' => 'Fajar Santoso',
                'guest_email' => 'fajar.s@example.com',
                'guest_phone' => '+62 878 2233 4455',
                'property_type' => 'suwung',
                'rental_type' => 'monthly',
                'duration' => 6,
                'start_date' => now()->subDays(20)->toDateString(),
                'end_date' => now()->addDays(160)->toDateString(),
                'total_price' => 9600000,
                'status' => 'active',
                'notes' => 'Staf perhotelan Sanur.',
            ]);
        }
    }
}
