<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CarDetail\Car;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        return view('.dashboard');
    }

    /**
     * Get dashboard statistics (AJAX)
     */
    public function getStats()
    {
        $user = Auth::user();
        $isAdmin = in_array($user->role, ['admin', 'manager']);
        
        if ($isAdmin) {
            // Admin sees all bookings
            $userId = null;
            $total = Booking::count();
            $pending = Booking::where('status', 'pending')->count();
            $active = Booking::whereIn('status', ['confirmed', 'active'])->count();
            $cancelled = Booking::where('status', 'cancelled')->count();
            
            // Status distribution for chart
            $statusData = [
                'pending' => Booking::where('status', 'pending')->count(),
                'confirmed' => Booking::where('status', 'confirmed')->count(),
                'active' => Booking::where('status', 'active')->count(),
                'returned' => Booking::where('status', 'returned')->count(),
                'completed' => Booking::where('status', 'completed')->count(),
                'cancelled' => Booking::where('status', 'cancelled')->count(),
            ];
            
            // Monthly trend (last 6 months)
            $trendData = $this->getMonthlyTrend(null);
            
            // Recent activity
            $recentActivity = $this->getRecentActivity(null);
            
        } else {
            // Regular user sees only their bookings
            $userId = $user->user_id ?? $user->id;
            
            $total = Booking::where('user_id', $userId)->count();
            $pending = Booking::where('user_id', $userId)->where('status', 'pending')->count();
            $active = Booking::where('user_id', $userId)->whereIn('status', ['confirmed', 'active'])->count();
            $cancelled = Booking::where('user_id', $userId)->where('status', 'cancelled')->count();
            
            $statusData = [
                'pending' => Booking::where('user_id', $userId)->where('status', 'pending')->count(),
                'confirmed' => Booking::where('user_id', $userId)->where('status', 'confirmed')->count(),
                'active' => Booking::where('user_id', $userId)->where('status', 'active')->count(),
                'returned' => Booking::where('user_id', $userId)->where('status', 'returned')->count(),
                'completed' => Booking::where('user_id', $userId)->where('status', 'completed')->count(),
                'cancelled' => Booking::where('user_id', $userId)->where('status', 'cancelled')->count(),
            ];
            
            $trendData = $this->getMonthlyTrend($userId);
            $recentActivity = $this->getRecentActivity($userId);
        }
        
        return response()->json([
            'success' => true,
            'total' => $total,
            'pending' => $pending,
            'active' => $active,
            'cancelled' => $cancelled,
            'status_data' => $statusData,
            'trend_data' => $trendData,
            'recent_activity' => $recentActivity,
        ]);
    }

    /**
     * Get recent activity (AJAX)
     */
    public function getRecentActivityData()
    {
        $user = Auth::user();
        $isAdmin = in_array($user->role, ['admin', 'manager']);
        $userId = $isAdmin ? null : ($user->user_id ?? $user->id);
        
        $activities = $this->getRecentActivity($userId);
        
        return response()->json($activities);
    }

    /**
     * Get monthly trend data
     */
    private function getMonthlyTrend($userId)
    {
        $months = [];
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        for ($i = 5; $i >= 0; $i--) {
            $month = $currentMonth - $i;
            $year = $currentYear;
            
            if ($month <= 0) {
                $month += 12;
                $year--;
            }
            
            $query = Booking::whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
            
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $months[$month - 1] = $query->count();
        }
        
        // Fill missing months with 0
        $result = [];
        for ($i = 0; $i < 12; $i++) {
            $result[$i] = $months[$i] ?? 0;
        }
        
        return $result;
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity($userId)
    {
        $query = Booking::with(['user', 'car'])
            ->orderBy('created_at', 'desc')
            ->limit(10);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->get()->map(function($booking) use ($userId) {
            return [
                'user' => $userId ? 'You' : ($booking->user->name ?? 'Unknown'),
                'car' => $booking->car->name ?? 'Unknown',
                'status' => $booking->status,
                'time' => $booking->created_at->diffForHumans(),
            ];
        });
    }
}