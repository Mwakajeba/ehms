<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel\Property;
use App\Models\Hotel\Room;
use App\Models\Hotel\Booking;
use App\Models\Hotel\Guest;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HotelManagementController extends Controller
{
    public function index()
    {
        $this->authorize('view hotel management');
        
        // Get summary statistics
        $totalProperties = Property::forBranch(current_branch_id())
            ->forCompany(current_company_id())
            ->byType('hotel')
            ->count();
            
        $totalRooms = Room::forBranch(current_branch_id())
            ->forCompany(current_company_id())
            ->count();
            
        $availableRooms = Room::forBranch(current_branch_id())
            ->forCompany(current_company_id())
            ->available()
            ->count();
            
        $totalBookings = Booking::forBranch(current_branch_id())
            ->forCompany(current_company_id())
            ->where('check_in', '>=', now()->startOfMonth())
            ->where('check_in', '<=', now()->endOfMonth())
            ->count();
            
        $totalGuests = Guest::forCompany(current_company_id())
            ->count();
            
        $currentOccupancy = $this->calculateCurrentOccupancy();
        $monthlyRevenue = $this->calculateMonthlyRevenue();
        
        // Rooms Occupied - count of currently occupied rooms
        $roomsOccupied = Booking::forBranch(current_branch_id())
            ->forCompany(current_company_id())
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>=', now())
            ->distinct('room_id')
            ->count('room_id');
        
        // Today's Bookings - bookings starting today (value and count)
        $todaysBookingsValue = Booking::forBranch(current_branch_id())
            ->forCompany(current_company_id())
            ->whereDate('check_in', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
        $todaysBookingsCount = Booking::forBranch(current_branch_id())
            ->forCompany(current_company_id())
            ->whereDate('check_in', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->count();

        return view('hotel.management.index', compact(
            'totalProperties',
            'totalRooms',
            'availableRooms',
            'totalBookings',
            'totalGuests',
            'currentOccupancy',
            'monthlyRevenue',
            'roomsOccupied',
            'todaysBookingsValue',
            'todaysBookingsCount'
        ));
    }

    private function calculateCurrentOccupancy()
    {
        $totalRooms = Room::forBranch(current_branch_id())
            ->forCompany(current_company_id())
            ->count();
            
        if ($totalRooms == 0) return 0;
        
        $occupiedRooms = Booking::forBranch(current_branch_id())
            ->forCompany(current_company_id())
            ->where('check_in', '<=', now())
            ->where('check_out', '>=', now())
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->distinct('room_id')
            ->count('room_id');
            
        return round(($occupiedRooms / $totalRooms) * 100, 2);
    }

    private function calculateMonthlyRevenue()
    {
        return Booking::forBranch(current_branch_id())
            ->forCompany(current_company_id())
            ->where('check_in', '>=', now()->startOfMonth())
            ->where('check_in', '<=', now()->endOfMonth())
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
    }
}