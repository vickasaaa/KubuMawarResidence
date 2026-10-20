<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Booking::with('room');

        // If authenticated user is regular user (not admin), show only their bookings
        if ($user && $user->role !== 'admin') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('guest_email', $user->email);
            });
        }

        // Filters for Admin
        if ($request->filled('property_type') && $request->property_type !== 'all') {
            $query->where('property_type', $request->property_type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $bookings,
            'meta' => [
                'total' => $bookings->count(),
                'active' => $bookings->whereIn('status', ['confirmed', 'active'])->count(),
                'completed' => $bookings->where('status', 'completed')->count(),
            ]
        ]);
    }

    /**
     * Store a new booking (from user dashboard or booking page).
     */
    public function store(Request $request)
    {
        if (!$request->filled('guest_phone') && $request->filled('phone')) {
            $request->merge(['guest_phone' => $request->phone]);
        }
        if (!$request->filled('guest_email') && $request->filled('email')) {
            $request->merge(['guest_email' => $request->email]);
        }
        if (!$request->filled('guest_email') && $request->user()) {
            $request->merge(['guest_email' => $request->user()->email]);
        }

        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:30',
            'rental_type' => 'required|in:weekly,monthly',
            'duration' => 'required|integer|min:1|max:36',
            'start_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data formulir reservasi belum lengkap.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $room = Room::findOrFail($request->room_id);

        // 1. Check room availability
        if ($room->status === 'occupied') {
            return response()->json([
                'status' => 'error',
                'message' => "Maaf, kamar {$room->room_number} ({$room->name}) saat ini sedang terisi.",
            ], 400);
        }

        // 2. Enforce rental rules per property
        if ($room->property_type === 'suwung' && $request->rental_type === 'weekly') {
            return response()->json([
                'status' => 'error',
                'message' => 'Kubu Mawar Suwung adalah kost-kostan yang hanya menerima penyewaan sistem BULANAN.',
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $duration = (int) $request->duration;
        $rentalType = $request->rental_type;

        // 3. Compute End Date & Total Price
        if ($rentalType === 'weekly') {
            $endDate = (clone $startDate)->addWeeks($duration);
            $weeklyRate = $room->price_weekly ?: round($room->price_monthly / 4);
            $totalPrice = $weeklyRate * $duration;
        } else {
            // monthly
            $endDate = (clone $startDate)->addMonths($duration);
            $totalPrice = $room->price_monthly * $duration;
        }

        // Generate clean booking code: BK-RES-20261015-XXXX or BK-SWG-20261015-XXXX
        $prefix = $room->property_type === 'suwung' ? 'BK-SWG' : 'BK-RES';
        $bookingCode = $prefix . '-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $userId = $request->user() ? $request->user()->id : null;

        $booking = Booking::create([
            'booking_code' => $bookingCode,
            'user_id' => $userId,
            'room_id' => $room->id,
            'guest_name' => $request->guest_name,
            'guest_email' => $request->guest_email,
            'guest_phone' => $request->guest_phone,
            'property_type' => $room->property_type,
            'rental_type' => $rentalType,
            'duration' => $duration,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_price' => $totalPrice,
            'status' => 'confirmed',
            'notes' => $request->notes,
        ]);

        // 4. Update room status to 'occupied' (terisi) instantly!
        $room->status = 'occupied';
        $room->save();

        return response()->json([
            'status' => 'success',
            'message' => "Reservasi berhasil dibuat! Kamar {$room->room_number} kini berstatus terisi.",
            'data' => [
                'booking' => $booking->load('room'),
                'room' => $room,
            ]
        ], 201);
    }

    /**
     * Admin updates booking status.
     */
    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::with('room')->find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data reservasi tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,active,completed,cancelled',
        ]);

        $oldStatus = $booking->status;
        $newStatus = $request->status;

        $booking->status = $newStatus;
        $booking->save();

        // Real-time synchronization with room:
        // If booking is finished or cancelled -> free up room (available / kosong)
        if (in_array($newStatus, ['completed', 'cancelled'])) {
            $hasOtherActive = Booking::where('room_id', $booking->room_id)
                ->where('id', '!=', $booking->id)
                ->whereIn('status', ['confirmed', 'active'])
                ->exists();

            if (!$hasOtherActive && $booking->room) {
                $booking->room->status = 'available';
                $booking->room->save();
            }
        } elseif (in_array($newStatus, ['confirmed', 'active'])) {
            // Ensure room is marked occupied
            if ($booking->room) {
                $booking->room->status = 'occupied';
                $booking->room->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Status reservasi {$booking->booking_code} diubah menjadi '{$newStatus}'.",
            'data' => $booking->fresh(['room']),
        ]);
    }
}
