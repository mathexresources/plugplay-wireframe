<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Showcase;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $valid = Order::where('status', '!=', 'cancelled');
        $since = now()->subDays(29)->startOfDay();
        $recent = (clone $valid)->where('created_at', '>=', $since)->get(['total', 'created_at']);

        $days = collect(range(0, 29))->map(function ($i) use ($since, $recent) {
            $d = $since->copy()->addDays($i);
            $dayOrders = $recent->filter(fn ($o) => $o->created_at->isSameDay($d));
            return ['date' => $d, 'revenue' => (int) $dayOrders->sum('total'), 'count' => $dayOrders->count()];
        });

        $top = OrderItem::selectRaw('name, sum(quantity) as qty, sum(price * quantity) as revenue')
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->groupBy('name')->orderByDesc('qty')->take(5)->get();

        $byStatus = Order::selectRaw('status, count(*) as cnt')->groupBy('status')->pluck('cnt', 'status');
        $byCar = Order::selectRaw("coalesce(car_label, 'bez auta') as car, count(*) as cnt")->groupBy('car')->orderByDesc('cnt')->take(5)->pluck('cnt', 'car');

        return view('admin.dashboard', [
            'stats' => [
                'revenue30' => (int) $recent->sum('total'),
                'orders30' => $recent->count(),
                'avg' => $recent->count() ? (int) round($recent->avg('total')) : 0,
                'revenueAll' => (int) (clone $valid)->sum('total'),
                'newOrders' => $byStatus['new'] ?? 0,
                'unpaid' => Order::where('is_paid', false)->where('status', '!=', 'cancelled')->count(),
                'customers' => User::where('is_admin', false)->count(),
                'products' => Product::active()->count(),
            ],
            'days' => $days, 'top' => $top, 'byStatus' => $byStatus, 'byCar' => $byCar,
            'lowStock' => Product::active()->where('stock', '<=', 5)->orderBy('stock')->take(8)->get(),
            'latest' => Order::latest()->take(8)->get(),
            'pending' => ['reviews' => Review::where('is_approved', false)->count(), 'showcases' => Showcase::where('is_approved', false)->count(), 'messages' => Message::where('is_read', false)->count()],
        ]);
    }
}
