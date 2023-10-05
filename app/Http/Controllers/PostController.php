<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with(['category', 'tags'])->latest()->paginate(10);

        return view('posts.index', [
            'posts' => $posts,
        ]);
    }

    public function show(Post $post)
    {
        return view('posts.post', [
            'post' => $post,
        ]);
    }

}