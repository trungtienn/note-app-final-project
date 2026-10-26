<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $query = Note::where('user_id', $userId);

        // Kiểm tra an toàn xem có quan hệ labels không trước khi load
        if (method_exists(Note::class, 'labels')) {
            $query->with('labels');
        }

        if ($request->filled('search')) {
            $keyword = '%' . $request->search . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', $keyword)
                  ->orWhere('content', 'LIKE', $keyword);
            });
        }

        $notes = $query->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'notes' => $notes,
        ]);
    }

    public function autoSave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['nullable'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'color' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $defaultColor = '#ffffff';
        if ($user && !empty($user->preferences)) {
            $prefs = is_array($user->preferences) ? $user->preferences : json_decode($user->preferences, true);
            $defaultColor = $prefs['default_color'] ?? '#ffffff';
        }

        $color = $validated['color'] ?? $defaultColor;

        if (!empty($validated['id'])) {
            $note = Note::where('user_id', Auth::id())->findOrFail($validated['id']);
            $note->update([
                'title' => $validated['title'],
                'content' => $validated['content'] ?? '',
                'color' => $color,
            ]);
        } else {
            $note = Note::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'content' => $validated['content'] ?? '',
                'color' => $color,
            ]);
        }

        return response()->json([
            'success' => true,
            'note' => $note,
            'message' => 'Đã tự động lưu',
        ]);
    }

    public function togglePin(Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $note->is_pinned = !$note->is_pinned;
        $note->pinned_at = $note->is_pinned ? now() : null;
        $note->save();

        return response()->json(['success' => true, 'is_pinned' => $note->is_pinned]);
    }

    public function destroy(Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $note->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa ghi chú']);
    }
}