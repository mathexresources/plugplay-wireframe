<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Product;
use App\Models\Showcase;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function form(Request $request)
    {
        $type = array_key_exists($request->query('typ'), Message::TYPES) ? $request->query('typ') : 'dotaz';
        return view('shop.contact', ['type' => $type, 'subject' => $request->query('predmet')]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:'.implode(',', array_keys(Message::TYPES)),
            'name' => 'required|string|max:120', 'email' => 'required|email|max:120',
            'subject' => 'nullable|string|max:160', 'body' => 'required|string|min:5|max:5000',
            'attachment' => 'nullable|image|max:5120',
            'website' => 'prohibited', // honeypot
        ]);
        unset($data['attachment'], $data['website']);
        if ($request->hasFile('attachment')) $data['attachment'] = $request->file('attachment')->store('messages', 'public');
        Message::create($data + ['user_id' => auth()->id()]);
        return back()->with('ok', 'Zprávu máme. Odpovídáme do 24 hodin v pracovní dny.');
    }

    public function showcases()
    {
        $showcases = Showcase::with('product')->where('is_approved', true)->latest()->paginate(9);
        $products = Product::active()->orderBy('name')->get(['id', 'name']);
        return view('shop.showcases', compact('showcases', 'products'));
    }

    public function submitShowcase(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120', 'author_name' => 'required|string|max:60',
            'product_id' => 'nullable|exists:products,id',
            'before_image' => 'required|image|max:5120', 'after_image' => 'required|image|max:5120',
        ]);
        Showcase::create([
            'title' => $data['title'], 'author_name' => $data['author_name'], 'product_id' => $data['product_id'] ?? null,
            'user_id' => auth()->id(),
            'before_image' => $request->file('before_image')->store('showcases', 'public'),
            'after_image' => $request->file('after_image')->store('showcases', 'public'),
        ]);
        return back()->with('ok', 'Díky! Fotky zkontrolujeme a zveřejníme. Slevový kód FOTKY10 ti dává 10 % na další nákup.');
    }
}
