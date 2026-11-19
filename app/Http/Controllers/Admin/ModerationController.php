<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Review;
use App\Models\Showcase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ModerationController extends Controller
{
    public function reviews(Request $request)
    {
        $reviews = Review::with('product')->when($request->query('stav') !== 'vse', fn ($q) => $q->orderBy('is_approved'))->latest()->paginate(25);
        return view('admin.moderation.reviews', compact('reviews'));
    }

    public function toggleReview(Review $review)
    {
        $review->update(['is_approved' => ! $review->is_approved]);
        return back()->with('ok', $review->is_approved ? 'Recenze je zveřejněná.' : 'Recenze je skrytá.');
    }

    public function deleteReview(Review $review)
    {
        $review->delete();
        return back()->with('ok', 'Recenze je smazaná.');
    }

    public function showcases()
    {
        $showcases = Showcase::with('product')->orderBy('is_approved')->latest()->paginate(24);
        return view('admin.moderation.showcases', compact('showcases'));
    }

    public function toggleShowcase(Showcase $showcase)
    {
        $showcase->update(['is_approved' => ! $showcase->is_approved]);
        return back()->with('ok', $showcase->is_approved ? 'Proměna je na webu.' : 'Proměna je skrytá.');
    }

    public function deleteShowcase(Showcase $showcase)
    {
        Storage::disk('public')->delete(array_filter([$showcase->before_image, $showcase->after_image]));
        $showcase->delete();
        return back()->with('ok', 'Proměna je smazaná.');
    }

    public function messages()
    {
        $messages = Message::latest()->paginate(25);
        return view('admin.moderation.messages', compact('messages'));
    }

    public function message(Message $message)
    {
        $message->update(['is_read' => true]);
        return view('admin.moderation.message', compact('message'));
    }

    public function deleteMessage(Message $message)
    {
        if ($message->attachment) Storage::disk('public')->delete($message->attachment);
        $message->delete();
        return redirect()->route('admin.messages.index')->with('ok', 'Zpráva je smazaná.');
    }
}
