<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents filtered by user's branches.
     */
    public function index(Request $request): Response
    {
        // Check if user can view any documents
        Gate::authorize('viewAny', Document::class);

        // Get user's branch IDs
        $userBranchIds = $request->user()->branches()->pluck('branches.id');

        // Query documents filtered by user's branches
        $documents = Document::query()
            ->with(['documentType', 'branch', 'uploadedBy'])
            ->whereIn('branch_id', $userBranchIds)
            ->latest('created_at')
            ->paginate(10)
            ->through(fn (Document $document) => [
                'id' => $document->id,
                'document_type_id' => $document->document_type_id,
                'branch_id' => $document->branch_id,
                'description' => $document->description,
                'expires_at' => $document->expires_at?->format('Y-m-d'),
                'file_path' => $document->file_path,
                'original_filename' => $document->original_filename,
                'file_size' => $document->file_size,
                'mime_type' => $document->mime_type,
                'uploaded_by' => $document->uploaded_by,
                'created_at' => $document->created_at->toISOString(),
                'updated_at' => $document->updated_at->toISOString(),
                'is_expired' => $document->isExpired(),
                'document_type' => [
                    'id' => $document->documentType->id,
                    'name' => $document->documentType->name,
                    'description' => $document->documentType->description,
                ],
                'branch' => [
                    'id' => $document->branch->id,
                    'name' => $document->branch->name,
                    'address' => $document->branch->address,
                    'contact_name' => $document->branch->contact_name,
                    'contact_email' => $document->branch->contact_email,
                    'contact_phone' => $document->branch->contact_phone,
                ],
                'uploaded_by_user' => [
                    'id' => $document->uploadedBy->id,
                    'name' => $document->uploadedBy->name,
                    'avatar' => $document->uploadedBy->avatar,
                ],
            ]);

        // Get all document types for filter dropdown
        $documentTypes = DocumentType::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('documents/index', [
            'documents' => $documents,
            'documentTypes' => $documentTypes,
        ]);
    }

    /**
     * Download a document file.
     */
    public function download(Document $document): StreamedResponse
    {
        // Check if user can download this document
        Gate::authorize('download', $document);

        // Check if file exists
        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        // Return file download response
        return Storage::disk('public')->download(
            $document->file_path,
            $document->original_filename
        );
    }
}
