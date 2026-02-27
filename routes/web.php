<?php

use App\Http\Controllers\Api\TransactionController;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/** ************* 1-m relation ************/
Route::get('1-m-child/{category}', function (Category $category) {
    return $category->books;
});
Route::get('1-m-parent/{book}', function (Book $book) {
    return $book->load('category');
    // $book = Book::where('ISBN', '1112223334445')->first();
    // return $book->category;
});
/** with */
Route::get('test', function () {
    $book = Book::find(8);
    Category::find(5)->books()->save($book);
});
Route::get('with', function () {
    $categories = Category::with('books')->get();
    return $categories;
});
/** load */
Route::get('load/{category}', function (Category $category) {
    return $category->load('books');
});
Route::get('load-collection', function () {
    $categories = Category::all();
    $categories->load('books');
    return $categories;
});

Route::get('1-m-child-where', function () {
    $category = Category::find(5);
    return $category->books()->where('price', '>=', 10)->get();
});
Route::get('1-m-child-update', function () {
    $category = Category::find(1);
    return $category->books()->update(['price' => 0]);
});
Route::get('1-m-child-delete', function () {
    $category = Category::find(1);
    dd($category->books());
    $category->books()->delete();
    $category->delete();
    return "category deleted successfuly";
});

Route::get('1-m-child-create', function () {
    $category =   Category::create([
        'name' => 'نفسية'
    ])->books()->create(
        [
            "ISBN" => "1112223334444",
            "title" => "تيسير الأمور في ملء القدور",
            "price" => 1,
            "mortgage" => 10,

        ]
    );
    return $category;
    // return "category created successfuly";
});

/** ************* m-m relation ************/
Route::get('m-m/{book}', function (Book $book) {
    return $book->authors;
});
Route::get('m-m-2/{author}', function (Author $author) {
    return $author->books;
});
Route::get('attach/{author}', function (Author $author) {
    $author->books()->attach(5);
    return $author->load('books');
});
Route::get('detach/{author}', function (Author $author) {
    $author->books()->detach(5);
    return $author->load('books');
});


Route::controller(TransactionController::class)->prefix('/transactions')->group(function () {
    Route::get('', 'index');
    Route::post('', 'store');
    Route::get('{id}', 'show');
    Route::post('{id}/return', 'returnBook');
});
