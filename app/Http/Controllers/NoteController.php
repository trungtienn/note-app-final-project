<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $query = Note::with(['labels', 'attachments'])
            ->where('user_id', $userId);

        // 1. Lọc theo nhãn được chọn (Label Filter)
        if ($request->filled('label_id')) {
            $labelId = $request->label_id;
            $query->whereHas('labels', function ($q) use ($labelId) {
                $q->where('labels.id', $labelId);
            });
        }

        // 2. Tìm kiếm tức thì (Live Search theo title hoặc content)
        if ($request->filled('search')) {
            $keyword = '%' . $request->search . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', $keyword)
                  ->orWhere('content', 'LIKE', $keyword);
            });
        }

        // 3. Sắp xếp: Ưu tiên Ghim lên đầu, sau đó đến thời gian cập nhật mới nhất
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

    /**
     * Upload một hoặc nhiều file đính kèm cho ghi chú.
     */
    public function uploadAttachments(Request $request, Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'files.*' => ['required', 'file', 'max:20480'], // Tối đa 20MB mỗi file
        ]);

        $uploadedAttachments = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $mimeType = $file->getMimeType();
                $originalName = $file->getClientOriginalName();
                $fileSize = $file->getSize();

                // Xác định loại file: image, video hoặc file thông thường
                $fileType = 'file';
                if (str_starts_with($mimeType, 'image/')) {
                    $fileType = 'image';
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $fileType = 'video';
                }

                // Lưu file vào thư mục storage/app/public/attachments
                $path = $file->store('attachments', 'public');

                $attachment = Attachment::create([
                    'note_id' => $note->id,
                    'file_path' => $path,
                    'file_name' => $originalName,
                    'file_type' => $fileType,
                    'file_size' => $fileSize,
                ]);

                $uploadedAttachments[] = [
                    'id' => $attachment->id,
                    'name' => $attachment->file_name,
                    'type' => $attachment->file_type,
                    'url' => asset('storage/' . $attachment->file_path),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'attachments' => $uploadedAttachments,
            'message' => 'Đã tải lên tệp đính kèm'
        ]);
    }

    /**
     * Xóa một file đính kèm khỏi ghi chú.
     */
    public function deleteAttachment(Attachment $attachment): JsonResponse
    {
        $note = $attachment->note;
        if (!$note || $note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Xóa file vật lý trong storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa tệp đính kèm']);
    }

    /**
     * Gắn hoặc gỡ nhãn cho ghi chú.
     */
    public function syncLabels(Request $request, Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['exists:labels,id'],
        ]);

        $note->labels()->sync($validated['label_ids'] ?? []);

        return response()->json([
            'success' => true,
            'note' => $note->fresh(['labels', 'attachments']),
            'message' => 'Đã cập nhật nhãn cho ghi chú',
        ]);
    }
}