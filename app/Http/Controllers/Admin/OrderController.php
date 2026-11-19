<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    private function filtered(Request $request)
    {
        $q = Order::query()->withCount('items');
        if ($t = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('number', 'like', "%$t%")->orWhere('name', 'like', "%$t%")->orWhere('email', 'like', "%$t%"));
        }
        if ($request->filled('stav')) $q->where('status', $request->query('stav'));
        if ($request->query('platba') === 'nezaplaceno') $q->where('is_paid', false);
        if ($request->query('platba') === 'zaplaceno') $q->where('is_paid', true);
        if ($request->filled('od')) $q->whereDate('created_at', '>=', $request->query('od'));
        if ($request->filled('do')) $q->whereDate('created_at', '<=', $request->query('do'));
        return $q->latest();
    }

    public function index(Request $request)
    {
        $orders = $this->filtered($request)->paginate(25)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function export(Request $request)
    {
        $orders = $this->filtered($request)->with('items')->get();
        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, ['Číslo', 'Datum', 'Zákazník', 'E-mail', 'Telefon', 'Auto', 'Doprava', 'Platba', 'Sleva', 'Celkem', 'Stav', 'Zaplaceno', 'Položky'], ';');
            foreach ($orders as $o) {
                fputcsv($out, [$o->number, $o->created_at->format('Y-m-d H:i'), $o->name, $o->email, $o->phone, $o->car_label, $o->shipping_name, $o->payment_name,
                    $o->discount, $o->total, $o->status_label, $o->is_paid ? 'ano' : 'ne',
                    $o->items->map(fn ($i) => $i->quantity.'× '.$i->name)->implode(', ')], ';');
            }
            fclose($out);
        }, 'objednavky-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(Order::STATUSES)),
            'tracking_number' => 'nullable|string|max:60', 'admin_note' => 'nullable|string|max:2000',
        ]);
        $old = $order->status;
        $order->update($data + ['is_paid' => $request->boolean('is_paid')]);
        if ($old !== $order->status && $request->boolean('notify')) {
            try {
                Mail::raw("Stav objednávky #{$order->number} se změnil na: {$order->status_label}.".($order->tracking_number ? "\nČíslo zásilky: {$order->tracking_number}" : '')
                    ."\n\n".route('checkout.done', ['order' => $order, 't' => $order->token]), fn ($m) => $m->to($order->email)->subject("Objednávka #{$order->number}: {$order->status_label}"));
            } catch (\Throwable $e) { report($e); }
        }
        return back()->with('ok', 'Objednávka je uložená.'.($old !== $order->status && $request->boolean('notify') ? ' Zákazníkovi jsme poslali e-mail.' : ''));
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('ok', "Objednávka #{$order->number} je smazaná.");
    }
}
