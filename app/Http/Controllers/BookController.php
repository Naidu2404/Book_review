<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     //The Request object gets the title from the user to display books with the title
    public function index(Request $request)
    {
        //we get the title from the request input object using the name 'title'
        $title = $request->input("title");
        $filter = $request->input("filter",'');

        //The when uses 2 args one is the title and if it is null it does nothing
        //but when it has a value which is not null it performs the mentioned function as the 2nd arg
        //the function takes a querybuilder object and runs a query which return the books with the title
        //we used the scope we defined earlier to get the book with the title
        $books = Book::when($title,
        fn($query, $title) => $query->title($title) );

        //we are adding teh filter to our query using match which is similar to switch but can return a value

        $books = match ($filter) {
            'popular_last_month' => $books->popularLastMonth(),
            'popular_last_6months' => $books->popularLast6Months(),
            'highest_rated_last_month' => $books->highestRatedLastMonth(),
            'highest_rated_last_6months' => $books->highestRatedLast6Months(),
            default => $books->latest()->withReviewsCount()->withAvgRating()
        };
            
        // $books = $books->get();

        //we use caching to remember this
        $cacheKey = 'books:' . $filter .':'. $title;
        $books = 
        // cache()->remember($cacheKey,3600, fn() => 
        $books->get()
    // )
    ;



        //we return the view name as books.index as all the views are named using this convention
        //we pass the books as a parameters to the view and can also be done using the compact('books') function
        return view('books.index',['books' => $books]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $cacheKey = 'book:' . $id;

        //we are caching the reviews of the book
        $book = cache()->remember($cacheKey,3600, fn() => 
            Book::with([
            'reviews' => fn($query) => $query->latest(),
            ])->withAvgRating()->withReviewsCount()->findOrFail($id));


        return view('books.show',['book'=> $book]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
