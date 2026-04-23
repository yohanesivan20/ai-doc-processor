<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Document;
use App\Jobs\ProcessDocumentJob;

class InvoiceController extends Controller
{
    //
    public function index()
    {
        $docs = Document::orderBy('created_at', 'desc')->get();

        if($docs->isEmpty()) {
            return response()->json([
                'message' => 'No documents found',
                'data' => []
            ]);
        }
        
        return response()->json([
            'message' => 'Documents retrieved',
            'data' => $docs
        ]);
    }

    public function show($id)
    {
        $doc = Document::find($id);

        if(!$doc) {
            return response()->json([
                'message' => 'Document not found',
                'data' => null
            ], 404);
        }

        if($doc->status !== 'done') {
            return response()->json([
                'message' => 'Document is still being processed',
                'data' => $doc
            ]);
        }

        return response()->json([
            'message' => 'Document retrieved',
            'data' => $doc
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,jpg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }


        $path = $request->file('file')->store('documents','public');

        $doc = Document::create([
            'file_path' => $path,
            'status' => 'pending'
        ]);

        ProcessDocumentJob::dispatch($doc);

        return response()->json([
            'message' => 'Uploaded & queued',
            'data' => $doc
        ]);
    }
}
