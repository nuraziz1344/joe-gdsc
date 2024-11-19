<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

class BookApiController extends Controller
{

    protected $database;
    protected $tablename;

    public function __construct(Database $database)
    {
        $this->database = $database; // Inject Firebase Database instance
        $this->tablename = 'books'; // Firebase node name
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'published_at' => 'required|date',
        ]);

        // Add timestamps for created_at and updated_at
        $timestamp = Carbon::now()->toISOString();

        // Retrieve the current highest ID
        $booksRef = $this->database->getReference($this->tablename);
        $books = $booksRef->getValue();

        $nextId = 1; // Default ID if no books exist
        if ($books) {
            $ids = array_keys($books); // Extract existing IDs
            $intIds = array_filter($ids, 'is_numeric'); // Filter only numeric IDs
            $nextId = !empty($intIds) ? max($intIds) + 1 : 1; // Increment the highest numeric ID
        }

        // Prepare the data
        $data = [
            'title' => $validatedData['title'],
            'author' => $validatedData['author'],
            'published_at' => $validatedData['published_at'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];

        // Save the data to Firebase with the custom ID
        $booksRef->getChild($nextId)->set($data);

        // Add the ID to the response data
        $data['id'] = $nextId;

        // Return the formatted response
        return response()->json([
            'message' => 'Book created successfully',
            'data' => $data,
        ], 201);
    }

    public function index()
    {
        // Retrieve all records from the Firebase "books" node
        $booksRef = $this->database->getReference($this->tablename);
        $books = $booksRef->getValue();

        // Check if data exists
        if (!$books || !is_array($books)) {
            return response()->json([
                'message' => 'No books found',
                'data' => [],
            ], 200);
        }

        // Format the data into an array of book objects
        $formattedBooks = [];
        foreach ($books as $id => $book) {
            if (is_array($book)) { // Ensure each record is an array
                $formattedBooks[] = array_merge($book, ['id' => (int)$id]);
            }
        }

        // Return the formatted response
        return response()->json([
            'data' => $formattedBooks,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        // Retrieve the specific book record by its ID
        $bookRef = $this->database->getReference("{$this->tablename}/{$id}");
        $book = $bookRef->getValue();

        // If the book doesn't exist, return a 404 response
        if (!$book) {
            return response()->json(["message" => "Book not found"], 404);
        }

        // Validate incoming data
        $validatedData = $request->validate([
            'title' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ]);

        // Add an updated_at timestamp
        $validatedData['updated_at'] = now()->toISOString();

        // Update the book record in Firebase
        $updatedBook = $bookRef->update(array_filter($validatedData));

        // Retrieve the updated data
        $updatedBook = $bookRef->getValue();

        // Return a successful response
        return response()->json([
            "message" => "Book updated successfully",
            "data" => array_merge($updatedBook, ['id' => (int)$id]),
        ], 200);
    }

    public function destroy($id)
    {
        // Reference the specific book in the Firebase database
        $bookRef = $this->database->getReference("{$this->tablename}/{$id}");
        $book = $bookRef->getValue();

        // Check if the book exists
        if (!$book) {
            return response()->json(["message" => "Book not found"], 404);
        }

        // Delete the book
        $bookRef->remove();

        // Return success message
        return response()->json(["message" => "Book deleted successfully"], 200);
    }

    public function show($id)
    {
        // Reference the specific book in the Firebase database
        $bookRef = $this->database->getReference("{$this->tablename}/{$id}");
        $book = $bookRef->getValue();

        // Check if the book exists
        if (!$book) {
            return response()->json(["message" => "Book not found"], 404);
        }

        // Include the ID in the response for consistency
        $book['id'] = (int)$id;

        // Return the book data
        return response()->json([
            "data" => $book,
        ], 200);
    }
}
