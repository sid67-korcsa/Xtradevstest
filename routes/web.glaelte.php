<?php

// DB Facade importalasa

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
  return view('home', [
    'posts' => Post::paginate(3),
  ]);
})->name('home');

Route::get('/post', function () {
  return view('post');
});

Route::get('/posts', function () {
  //   $posts = DB::table('posts')->get();
  //   dd($posts[2]->title);
  //   dd($posts->pluck('title'));
  //   $posts = DB::table('posts')->first();
  $posts = DB::table('posts')->select('title', 'body')->find(4);
  dd($posts);
});

Route::get('/posts-count', function () {
  $postsCount = DB::table('posts')->count();
  dd($postsCount);
});

Route::get('/eloquent-post', function () {
  $post = Post::where('id', 3)->get();
  dd($post);
});

Route::get('/eloquent-posts-with-comments', function () {
  $posts = Post::withOnly([
    'comments'// => fn ($query) => $query->latest()->first()
  ])->paginate(2);
//   $posts = Post::with('latestComment')->paginate(2); // csak Laravel 11-ben működik
  dump($posts[0]->comments);
  dump($posts[1]->comments);
});

Route::get('/filtered-posts', function () {
  $posts = DB::table('posts')
    // ->where('published_at', '<', '2023-04-11')
    // ->where('published_at', '>', '2023-04-09')
    ->whereBetween('published_at', ['2023-04-09', '2023-04-11'])
    // ->whereNotNull('updated_at')
    ->orWhere('title', 'LIKE', '%th%')
    ->get();
  dd($posts);
});

Route::get('/posts-by-day', function () {
  $posts = DB::table('posts')
    //   ->select(DB::raw('DATE(published_at) as day, COUNT(*) AS posts_by_day'))
    ->selectRaw('DATE(published_at) as day, COUNT(*) AS posts_by_day')
    ->groupBy('day')
    ->having('day', '<>', '2023-04-10')
    ->get();
  dd($posts);
});

Route::get('/latest-posts', function () {
  $posts = DB::table('posts')
    ->orderBy('published_at', 'desc')
    ->get();
  dd($posts);
});

Route::get('/latest-post', function () {
  $post = DB::table('posts')
    ->latest()
    ->first();
  dd($post);
});

Route::get('/random-post', function () {
  $post = DB::table('posts')
    ->inRandomOrder()
    ->first();
  dd($post);
});

Route::get('/latest-posts-sql', function () {
  $posts = DB::table('posts')
    ->orderBy('published_at', 'desc')
    ->toSql();
  dd($posts);
});

Route::get('/insert-post', function () {
  DB::table('posts')->insert([
    'title' => fake()->sentence(1),
    'slug' => fake()->slug(2),
    'body' => fake()->paragraph(3),
    'published_at' => fake()->dateTime(),
    'created_at' => now(),
    'updated_at' => now(),
  ]);
});

Route::get('/insert-posts', function () {
  $postsCount = fake()->randomDigitNotNull();
  $posts = [];
  for ($i = 0; $i < $postsCount; $i++) {
    $posts[] = [
      'title' => fake()->sentence(1),
      'slug' => fake()->slug(2),
      'body' => fake()->paragraph(3),
      'published_at' => fake()->dateTime(),
      'created_at' => now(),
      'updated_at' => now(),
    ];
  }
  DB::table('posts')->insert($posts);
});

Route::get('/update-posts', function () {
  DB::table('posts')
    ->where('created_at', '>', now()->subDays(1)->endOfDay())
    ->update(['published_at' => now()]);
});

Route::get('/delete-latest-post', function () {
  DB::table('posts')
    ->orderBy('id', 'desc')
    ->limit(1)
    ->delete();
});

Route::get('/delete-posts', function () {
  //   DB::table('posts')->delete();
  DB::table('posts')->truncate();
});

Route::get('/post/{post}/rating', function ($post) {
  $result = DB::table('ratings')
    ->join('posts', 'ratings.id', '=', 'posts.rating_id')
    ->select('title', 'body', 'score')
    ->where('post_id', $post)
    ->first();
  dd($result);
});
// azure teszt

// Route::get('/categories/create', [CategoryController::class, 'create']);
// Route::post('/categories', [CategoryController::class, 'store']);

// Route::get('/categories/{id}/edit', [CategoryController::class, 'edit']);
// Route::put('/categories/{id}', [CategoryController::class, 'update']);

// Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

// Route::get('/categories', [CategoryController::class, 'index']);
// Route::get('/categories/{id}', [CategoryController::class, 'show']);

Route::resource('categories', CategoryController::class);

Route::resource('posts', PostController::class);
// Route::get('/posts/{post:slug}',[PostController::class, 'show'])->name('posts.show');
Route::get('/posts/{category:name}/{from}/{to?}', [PostController::class, 'getCategorysPostsFromTo']);

Route::resource('tags', TagController::class);

// Route::resource('comments', CommentController::class)->middleware('auth');

Route::get('/comments', [CommentController::class, 'index'])
  ->can('viewAny', App\Models\Comment::class)
  ->name('comments.index');

Route::middleware('can:create')->group(function () {
  Route::get('/comments/create', [CommentController::class, 'create'])
    ->name('comments.create');
  Route::post('/comments', [CommentController::class, 'store'])
    ->name('comments.store');
});

Route::get('/comments/{comment}', [CommentController::class, 'show'])
  ->can('view')
  ->name('comments.show');

Route::middleware('can:update')->group(function () {
  Route::get('/comments/{comment}/edit', [CommentController::class, 'edit'])
    ->name('comments.edit');
  Route::put('/comments/{comment}', [CommentController::class, 'update'])
    ->name('comments.update');
});

Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
  ->can('delete', 'comment')
  ->name('comments.destroy');

Route::resource('projects', ProjectController::class)->only('store', 'update', 'destroy');

Route::get('/profile', function () {
  return "My profile";
})->middleware('auth');

Route::get('/admin-panel', function () {
  if (Gate::allows('admin')) {
    return "Admin Panel";
  }
  abort(403);
});
