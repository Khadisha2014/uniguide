<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UniversityController extends Controller
{
    public function index(Request $request): View
    {
        $universities = University::query()
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")->orWhere('country', 'like', "%{$search}%"))
            ->latest()->paginate(12)->withQueryString();

        return view('admin.universities.index', compact('universities'));
    }

    public function create(): View
    {
        return view('admin.universities.form', ['university' => new University]);
    }

    public function store(Request $request): RedirectResponse
    {
        University::create($this->validated($request));

        return redirect()->route('admin.universities.index')->with('success', 'Университет добавлен.');
    }

    public function edit(University $university): View
    {
        return view('admin.universities.form', compact('university'));
    }

    public function update(Request $request, University $university): RedirectResponse
    {
        $university->update($this->validated($request));

        return redirect()->route('admin.universities.index')->with('success', 'Данные обновлены.');
    }

    public function destroy(University $university): RedirectResponse
    {
        $university->delete();

        return back()->with('success', 'Университет удалён.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'], 'short_name' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'], 'country' => ['required', 'string', 'max:100'],
            'flag' => ['nullable', 'string', 'max:10'], 'world_rank' => ['required', 'integer', 'min:1'],
            'acceptance_rate' => ['required', 'integer', 'between:0,100'], 'international_rate' => ['required', 'integer', 'between:0,100'],
            'tuition' => ['required', 'string', 'max:100'], 'tuition_value' => ['required', 'integer', 'min:0'],
            'requirements_text' => ['required', 'string'], 'deadline' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:100'], 'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'description' => ['nullable', 'string', 'max:1000'], 'is_published' => ['nullable', 'boolean'],
        ]);
        $data['requirements'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $data['requirements_text']))));
        $data['is_published'] = $request->boolean('is_published');
        unset($data['requirements_text']);

        return $data;
    }
}
