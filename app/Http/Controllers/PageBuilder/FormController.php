<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function submit(Request $request, Page $page)
    {
        $validated = $request->validate([
            'form_name' => 'nullable|string|max:255',
            'data' => 'required|array',
            'data.*' => 'nullable|string|max:5000',
        ]);

        $page->formSubmissions()->create([
            'form_name' => $validated['form_name'] ?? 'Contact Form',
            'data' => $validated['data'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Form submitted successfully',
        ]);
    }

    public function submissions(Request $request, Page $page)
    {
        $this->authorize('view', $page);

        $submissions = $page->formSubmissions()->paginate(20);

        return response()->json($submissions);
    }
}
