<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    public function submit(Request $request, Page $page)
    {
        $validated = $request->validate([
            'form_name' => 'nullable|string|max:255',
            'data' => 'required|array',
            'data.*' => 'nullable|string|max:5000',
        ]);

        DB::table('form_submissions')->insert([
            'page_id' => $page->id,
            'form_name' => $validated['form_name'] ?? 'Contact Form',
            'data' => json_encode($validated['data']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Form submitted successfully',
        ]);
    }

    public function submissions(Request $request, Page $page)
    {
        $this->authorize('view', $page);

        $submissions = DB::table('form_submissions')
            ->where('page_id', $page->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($submissions);
    }
}
