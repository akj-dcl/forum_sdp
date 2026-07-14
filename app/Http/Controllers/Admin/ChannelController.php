<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ChannelController extends Controller
{
    public function index(Request $request)
    {
        $channels = Channel::when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/datamaster/channel/Index', [
            'channelsData' => $channels,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:channels,name',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7', // Hex color e.g. #3525cd
        ]);

        Channel::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'color' => $request->color,
        ]);

        return redirect()->route('channels.index')->with('success', 'Saluran berhasil dibuat.');
    }

    public function update(Request $request, Channel $channel)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:channels,name,' . $channel->id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
        ]);

        $channel->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'color' => $request->color,
        ]);

        return redirect()->route('channels.index')->with('success', 'Saluran berhasil diperbarui.');
    }

    public function destroy(Channel $channel)
    {
        $channel->delete();

        return redirect()->route('channels.index')->with('success', 'Saluran berhasil dihapus.');
    }
}
