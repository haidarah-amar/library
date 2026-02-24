<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory;

     protected $fillable = ['ISBN' , 'title' , 'price' , 'mortgage' ,'pages',
    'borrow_duration','total_copies','stock','authorship_date' , 'cover','category_id' ];
 
    //  protected $guraded = [];


    function category(){
        return $this->belongsTo(Category::class);
    }
    function authors(){
        return $this->belongsToMany(Author::class);
    }

    public function scopeSearch($query, array $filters = [])
    {
        return $query
            ->when(isset($filters['title']) && $filters['title'], function ($q) use ($filters) {
                return $q->where('title', 'like', "%{$filters['title']}%");
            })
            ->when(isset($filters['category_name']) && $filters['category_name'], function ($q) use ($filters) {
                return $q->whereHas('category', function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', "%{$filters['category_name']}%");
                });
            })
            ->when(isset($filters['author_name']) && $filters['author_name'], function ($q) use ($filters) {
                return $q->whereHas('authors', function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', "%{$filters['author_name']}%");
                });
            });
    }

     public function rating()
    {
        return $this->belongsToMany(Customer::class, 'ratings')
            ->withPivot('rate')
            ->withTimestamps();
    }

    public function transactions()
{
    return $this->hasMany(Transaction::class);
}
}
