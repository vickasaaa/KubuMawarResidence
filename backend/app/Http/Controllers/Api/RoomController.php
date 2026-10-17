<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /**
     * Display a listing of rooms (with optional filters: property_type, status).
     */
    public function index(Request $request)
    {
        $query = Room::query()->with('activeBooking');

        // Filter by property type: 'residence' | 'suwung'
        if ($request->filled('property_type') && $request->property_type !== 'all') {
            $query->where('property_type', $request->property_type);
        }

        // Filter by status: 'available' | 'occupied' | 'maintenance'
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by keyword
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('room_number', 'like', "%{$search}%")
                  ->orWhere('type_label', 'like', "%{$search}%");
            });
        }

        $rooms = $query->orderBy('room_number')->get();

        // Summary counts
        $totalResidence = Room::where('property_type', 'residence')->count();
        $residenceAvailable = Room::where('property_type', 'residence')->where('status', 'available')->count();
        $residenceOccupied = Room::where('property_type', 'residence')->where('status', 'occupied')->count();

        $totalSuwung = Room::where('property_type', 'suwung')->count();
        $suwungAvailable = Room::where('property_type', 'suwung')->where('status', 'available')->count();
        $suwungOccupied = Room::where('property_type', 'suwung')->where('status', 'occupied')->count();

        return response()->json([
            'status' => 'success',
            'data' => $rooms,
            'meta' => [
                'total' => $rooms->count(),
                'residence' => [
                    'total' => $totalResidence,
                    'available' => $residenceAvailable,
                    'occupied' => $residenceOccupied,
                ],
                'suwung' => [
                    'total' => $totalSuwung,
                    'available' => $suwungAvailable,
                    'occupied' => $suwungOccupied,
                ],
            ]
        ]);
    }

    /**
     * Display a single room.
     */
    public function show($id)
    {
        $room = Room::with('activeBooking')->find($id);

        if (!$room) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kamar tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $room,
        ]);
    }

    /**
     * Store a newly created room.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_type' => 'required|in:residence,suwung',
            'room_number' => 'required|string|unique:rooms,room_number',
            'name' => 'required|string|max:255',
            'type_label' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'price_weekly' => 'nullable|numeric|min:0',
            'price_monthly' => 'required|numeric|min:0',
            'price_daily' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:available,occupied,maintenance',
            'features' => 'nullable|array',
            'max_guests' => 'nullable|integer|min:1',
            'size_sqm' => 'nullable|integer',
            'floor' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Enforce property rules:
        // Residence: homestay, weekly & monthly allowed
        // Suwung: kost, monthly only
        if ($data['property_type'] === 'suwung') {
            $data['property_name'] = 'Kubu Mawar Suwung';
            $data['property_category'] = 'kost';
            $data['allowed_rental_types'] = 'monthly';
            $data['price_weekly'] = null; // Suwung tidak boleh per minggu
        } else {
            $data['property_name'] = 'Kubu Mawar Residence';
            $data['property_category'] = 'homestay';
            $data['allowed_rental_types'] = 'weekly,monthly';
        }

        $room = Room::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Kamar berhasil ditambahkan.',
            'data' => $room,
        ], 201);
    }

    /**
     * Update room details.
     */
    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kamar tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'room_number' => 'sometimes|required|string|unique:rooms,room_number,' . $id,
            'name' => 'sometimes|required|string|max:255',
            'type_label' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'price_weekly' => 'nullable|numeric|min:0',
            'price_monthly' => 'sometimes|required|numeric|min:0',
            'price_daily' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:available,occupied,maintenance',
            'features' => 'nullable|array',
            'max_guests' => 'nullable|integer|min:1',
            'size_sqm' => 'nullable|integer',
            'floor' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // If suwung, ensure price_weekly stays null
        if ($room->property_type === 'suwung') {
            $data['price_weekly'] = null;
            $data['allowed_rental_types'] = 'monthly';
        }

        $room->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Data kamar berhasil diperbarui.',
            'data' => $room->fresh(),
        ]);
    }

    /**
     * Quick toggle status: available (kosong) <-> occupied (terisi) <-> maintenance
     */
    public function updateStatus(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kamar tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        $oldStatus = $room->status;
        $newStatus = $request->status;

        $room->status = $newStatus;
        $room->save();

        // If changed to available, complete active bookings
        if ($newStatus === 'available') {
            Booking::where('room_id', $room->id)
                ->whereIn('status', ['confirmed', 'active'])
                ->update(['status' => 'completed']);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Status kamar {$room->room_number} berhasil diubah dari '{$oldStatus}' menjadi '{$newStatus}'.",
            'data' => $room->fresh(),
        ]);
    }

    /**
     * Remove the specified room.
     */
    public function destroy($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kamar tidak ditemukan.',
            ], 404);
        }

        $room->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kamar berhasil dihapus.',
        ]);
    }
}
