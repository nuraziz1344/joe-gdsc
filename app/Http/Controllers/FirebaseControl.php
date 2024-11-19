<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Database;

class FirebaseControl extends Controller
{

    protected $database;
    protected $tablename;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->tablename = 'books';
    }

    public function index() {
        $reference = $this->database->getReference($this->tablename)->getValue();
        return view('coba', compact('reference'));
    }

    public function create() {
        return view('tambah');
    }

    public function store(Request $request) {
        $postdata = [
            'title' => $request->title,
            'author' => $request->author,
        ];
        $postRef = $this->database->getReference($this->tablename)->push($postdata);
        if($postRef) {
            return redirect()->route('index')->with('success', 'data berhasil di update');
        } else {
            return redirect()->route('index')->with('status', 'contact not added');
        }
    }
}
