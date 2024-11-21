<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="GDSC Test API",
 *     description="API documentation for the GDSC Test collection"
 * )
 * @OA\Server(
 *     url="https://joe-gdsc-8be20852b361.herokuapp.com/",
 *     description="API Server"
 * )
 *   @OA\Tag(
 *     name="Books",
 *     description="API operations related to books"
 * )
 */

class BookApiController extends Controller
{

    protected $database;
    protected $tablename;

    public function __construct(Database $database)
    {
        $this->database = $database; // Inject Firebase Database instance
        $this->tablename = 'books'; // Firebase node name
    }

    /**
     * @OA\Post(
     *     path="/api/books",
     *     tags={"Books"},
     *     summary="Create Book",
     *     description="Creates a new book entry.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "author", "published_at"},
     *             @OA\Property(property="title", type="string", example="5 Minute to Learn Go"),
     *             @OA\Property(property="author", type="string", example="Sundar Pichai"),
     *             @OA\Property(property="published_at", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Book created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string", example="5 Minute to Learn Go"),
     *                 @OA\Property(property="author", type="string", example="Sundar Pichai"),
     *                 @OA\Property(property="published_at", type="string", format="date"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */

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

    /**
     * @OA\Get(
     *     path="/api/books",
     *     tags={"Books"},
     *     summary="Read Books",
     *     description="Retrieves all book entries.",
     *     @OA\Response(
     *         response=200,
     *         description="List of books",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="author", type="string"),
     *                     @OA\Property(property="published_at", type="string", format="date"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

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

    /**
     * @OA\Put(
     *     path="/api/books/{bookId}",
     *     tags={"Books"},
     *     summary="Update Book",
     *     description="Updates the details of a specific book.",
     *     @OA\Parameter(
     *         name="bookId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Updated Title")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string", example="Updated Title"),
     *                 @OA\Property(property="author", type="string"),
     *                 @OA\Property(property="published_at", type="string", format="date"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    
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

    /**
     * @OA\Delete(
     *     path="/api/books/{bookId}",
     *     tags={"Books"},
     *     summary="Delete Book",
     *     description="Deletes a specific book.",
     *     @OA\Parameter(
     *         name="bookId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */

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


    /**
     * @OA\Get(
     *     path="/api/books/{bookId}",
     *     tags={"Books"},
     *     summary="Get Book",
     *     description="Retrieves a specific book by ID.",
     *     @OA\Parameter(
     *         name="bookId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Book not found")
     *         )
     *     )
     * )
     */

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
