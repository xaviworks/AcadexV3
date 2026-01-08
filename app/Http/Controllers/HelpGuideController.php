<?php

namespace App\Http\Controllers;

use App\Models\HelpGuide;
use App\Models\HelpGuideAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HelpGuideController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display help guides for the current user's role.
     */
    public function index()
    {
        $user = Auth::user();
        
        $guides = HelpGuide::with('attachments')
            ->active()
            ->visibleToRole($user->role)
            ->ordered()
            ->get();

        return view('help-guides.index', compact('guides'));
    }

    /**
     * Show a single help guide.
     */
    public function show(HelpGuide $helpGuide)
    {
        $user = Auth::user();
        
        // Check if guide is active and visible to user's role
        if (!$helpGuide->is_active || !$helpGuide->isVisibleToRole($user->role)) {
            abort(404);
        }

        $helpGuide->load('attachments');

        return view('help-guides.show', compact('helpGuide'));
    }

    /**
     * Download the attachment.
     */
    public function download(HelpGuide $helpGuide)
    {
        $user = Auth::user();
        
        // Check if guide is active and visible to user's role
        if (!$helpGuide->is_active || !$helpGuide->isVisibleToRole($user->role)) {
            abort(404);
        }

        if (!$helpGuide->hasAttachment()) {
            abort(404, 'No attachment found.');
        }

        if (!Storage::disk('public')->exists($helpGuide->attachment_path)) {
            abort(404, 'Attachment file not found.');
        }

        return Storage::disk('public')->download(
            $helpGuide->attachment_path,
            $helpGuide->attachment_name
        );
    }

    /**
     * Preview the PDF attachment inline.
     */
    public function preview(HelpGuide $helpGuide)
    {
        $user = Auth::user();
        
        // Check if guide is active and visible to user's role
        if (!$helpGuide->is_active || !$helpGuide->isVisibleToRole($user->role)) {
            abort(404);
        }

        if (!$helpGuide->hasAttachment()) {
            abort(404, 'No attachment found.');
        }

        if (!Storage::disk('public')->exists($helpGuide->attachment_path)) {
            abort(404, 'Attachment file not found.');
        }

        // Return the PDF inline for preview
        $file = Storage::disk('public')->get($helpGuide->attachment_path);
        
        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $helpGuide->attachment_name . '"');
    }

    /**
     * Preview an individual attachment inline.
     */
    public function previewAttachment(HelpGuideAttachment $attachment)
    {
        $user = Auth::user();
        $helpGuide = $attachment->helpGuide;
        
        // Check if guide is active and visible to user's role
        if (!$helpGuide->is_active || !$helpGuide->isVisibleToRole($user->role)) {
            abort(404);
        }

        if (!$attachment->fileExists()) {
            abort(404, 'Attachment file not found.');
        }

        // Return the PDF inline for preview
        $file = $attachment->getContents();
        
        return response($file, 200)
            ->header('Content-Type', $attachment->mime_type)
            ->header('Content-Disposition', 'inline; filename="' . $attachment->file_name . '"');
    }

    /**
     * Download an individual attachment.
     */
    public function downloadAttachment(HelpGuideAttachment $attachment)
    {
        $user = Auth::user();
        $helpGuide = $attachment->helpGuide;
        
        // Check if guide is active and visible to user's role
        if (!$helpGuide->is_active || !$helpGuide->isVisibleToRole($user->role)) {
            abort(404);
        }

        if (!$attachment->fileExists()) {
            abort(404, 'Attachment file not found.');
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }
}
