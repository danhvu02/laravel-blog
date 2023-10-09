<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('category')->paginate(10);

        return view('admin.posts.index', [
            'posts' => $posts,
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.posts.create', compact('categories'));
    }

    public function store(CreatePostRequest $request)
    {
        $tags = array_map('trim', explode(',', $request->tags));

        /* if ($request->hasFile('image')){
            $filename = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('uploads', $filename, 'public');
        } */
        $post = auth()->user()->posts()->create([
            'title' => $request->title,
            //'image' => $filename ?? null,
            'post' => $request->post,
            'category_id' => $request->category
        ]);

        if($request->hasFile('image')){
            $post->addMediaFromRequest('image')->toMediaCollection('images');
        } else {
            // Adding default image
            $post->addMedia(storage_path('defaults/defaultPostImage.jpg'))->preservingOriginal()->toMediaCollection();
        }

        foreach($tags as $tagName){
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $post->tags()->attach($tag);
        }

        return redirect()->route('admin.posts.index');
    }

    public function edit(Post $post)
    {
        $categories = Category::all();
        $tags = $post->tags->implode('name', ', ');

        return view('admin.posts.edit', compact('categories', 'post', 'tags'));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $tags = array_map('trim', explode(',', $request->tags));

        /* if ($request->hasFile('image')){
            Storage::delete('public/uploads/' . $post->image);

            $filename = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('uploads', $filename, 'public');
        } */
        
        $post->update([
            'title' => $request->title,
            //'image' => $filename ?? $post->image,
            'post' => $request->post,
            'category_id' => $request->category
        ]);

        if($request->hasFile('image')){
            $post->addMediaFromRequest('image')->toMediaCollection('images');
        }

        $newTags = [];
        foreach ($tags as $tagName) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            array_push($newTags, $tag->id);
        }

        $post->tags()->sync($newTags);

        return redirect()->route('admin.posts.index');
    }

    public function destroy(Post $post)
    {
        if ($post->image){
            Storage::delete('public/uploads/' . $post->image);
        }
        $post->tags()->detach();
        $post->delete();
        return redirect()->route('admin.posts.index');
    }
}
