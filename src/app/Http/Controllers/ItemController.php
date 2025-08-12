<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ExhibitionRequest;
use App\Http\Requests\CommentRequest;

 
class ItemController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab');
        $keyword = $request->query('keyword'); 

        if ($tab === 'mylist') {
            if (auth()->check()) {
                $query = auth()->user()->likedItems()->with('images');

                if ($keyword) {
                    $query->where('title', 'like', '%' . $keyword . '%');
                }

                $items = $query->latest()->get();
            } else {
                $items = collect();
            }
        } else {
            $query = Item::query()
                ->when(auth()->check(), function ($q) {
                    $q->where('user_id', '!=', auth()->id());
                })
                ->with('images');

            if ($keyword) {
                $query->where('title', 'like', '%' . $keyword . '%');
            }

            $items = $query->latest()->get();
        }

        return view('items.index', compact('items', 'tab', 'keyword'));
    }

    public function show(Item $item)
    {
        $item->load(['comments.user', 'categories', 'images']);

        return view('items.show', compact('item'));
    }

    public function toggleLike(Item $item)
    {
        $userId = Auth::id();

        $like = Like::where('item_id', $item->id)
                    ->where('user_id', $userId)
                    ->first();

        if ($like) {
            $like->delete();
        } else {
            Like::create([
                'item_id' => $item->id,
                'user_id' => $userId,
            ]);
        }

        return redirect()->back();
    }

    public function comment(CommentRequest $request, Item $item)
    {
        if ($item->user_id === Auth::id()) {
            return redirect()->back()->with('comment_error', '自分の商品にはコメントできません。');
        }
        Comment::create([
            'item_id' => $item->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return redirect("/item/{$item->id}#comment-section");
    }

     public function exhibition()
    {
        $categories = \App\Models\Category::all();

        return view('items.exhibition', compact('categories'));
    }

    public function store(ExhibitionRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        $item = new Item();
        $item->user_id = $user->id;
        $item->title = $validated['title'];
        $item->brand = $validated['brand'] ?? null;
        $item->description = $validated['description'];
        $item->price = $validated['price'];
        $item->condition = $validated['condition'];
        $item->save();
        $item->categories()->attach($validated['categories']);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('items', 'public');

                $item->item_images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect('/');
    }

}
