<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NoticeController extends Controller
{
    public function index()
    {
        $items = Notice::latest('date')->paginate(20);
        return view('admin.settings.notices.index', compact('items'));
    }

    public function create()
    {
        return view('admin.settings.notices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'    => 'nullable|date',
            'title'   => 'required|string|max:255',
            'details' => 'nullable|string',
            'file'    => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('file')) {
            $data['file'] = $this->saveFile($request->file('file'));
        }

        Notice::create($data);
        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice created successfully.');
    }

    public function edit($id)
    {
        $item = Notice::findOrFail($id);
        return view('admin.settings.notices.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Notice::findOrFail($id);
        $data = $request->validate([
            'date'    => 'nullable|date',
            'title'   => 'required|string|max:255',
            'details' => 'nullable|string',
            'file'    => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('file')) {
            // Delete old file if it exists
            $this->deleteFile($item->file);
            $data['file'] = $this->saveFile($request->file('file'));
        } else {
            unset($data['file']);
        }

        $item->update($data);
        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    public function destroy($id)
    {
        $item = Notice::findOrFail($id);
        $this->deleteFile($item->file);
        $item->delete();
        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice deleted successfully.');
    }

    /**
     * Saves into public/notices/ and returns the path used with asset(),
     * e.g. notices/20261002_153045_admission-notice.pdf
     */
    private function saveFile(UploadedFile $file): string
    {
        $dir = public_path('notices');
        File::ensureDirectoryExists($dir, 0775);

        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'notice';
        $name = now()->format('Ymd_His') . '_' . Str::limit($base, 80, '') . '.' . strtolower($file->getClientOriginalExtension());

        $file->move($dir, $name);
        return 'notices/' . $name;
    }

    /** Only removes files inside public/notices/, never other files under public/. */
    private function deleteFile(?string $path): void
    {
        if ($path && Str::startsWith($path, 'notices/') && !Str::contains($path, '..')) {
            File::delete(public_path($path));
        }
    }
}
