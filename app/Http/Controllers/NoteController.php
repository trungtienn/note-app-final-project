<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        //Query ghi chú do chính mình tạo
        $myNotesQuery = Note::with(['labels', 'attachments', 'shares'])
            ->where('user_id', $user->id);

        //Query ghi chú được người khác chia sẻ với mình
        $sharedNotesQuery = $user->sharedNotes()
            ->with(['labels', 'attachments', 'shares', 'user:id,display_name,email']);

        //Áp dụng bộ lọc nhãn (Label Filter) cho cả 2 query
        if ($request->filled('label_id')) {
            $labelId = $request->label_id;
            $myNotesQuery->whereHas('labels', function ($q) use ($labelId) {
                $q->where('labels.id', $labelId);
            });
            $sharedNotesQuery->whereHas('labels', function ($q) use ($labelId) {
                $q->where('labels.id', $labelId);
            });
        }

        //Áp dụng tìm kiếm tức thì (Live Search) cho cả 2 query
        if ($request->filled('search')) {
            $keyword = '%' . $request->search . '%';
            $filter = function ($q) use ($keyword) {
                $q->where('title', 'LIKE', $keyword)
                  ->orWhere('content', 'LIKE', $keyword);
            };
            $myNotesQuery->where($filter);
            $sharedNotesQuery->where($filter);
        }

        //Định dạng danh sách ghi chú của tôi (giữ nguyên logic mã hóa mật khẩu)
        $myNotes = $myNotesQuery->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($note) {
                $hasPassword = !empty($note->password_hash);

                if ($hasPassword) {
                    $note->content = '🔒 Ghi chú này đã được bảo vệ bằng mật khẩu.';
                    $note->setRelation('attachments', collect([]));
                }

                $note->is_owner = true;
                $note->permission = 'owner';
                $note->is_shared = $note->shares->count() > 0;
                $note->has_password = $hasPassword;

                unset($note->password_hash);
                return $note;
            });

        //Định dạng danh sách ghi chú được chia sẻ với tôi
        $sharedNotes = $sharedNotesQuery->orderByDesc('updated_at')
            ->get()
            ->map(function ($note) {
                $note->is_owner = false;
                $note->permission = $note->pivot->permission ?? 'view';
                $note->is_shared = true;
                $note->has_password = false;
                return $note;
            });

        return response()->json([
            'notes' => $myNotes,
            'shared_notes' => $sharedNotes,
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
            $note = Note::findOrFail($validated['id']);

            // Kiểm tra: Phải là chủ sở hữu HOẶC có quyền 'edit' được chia sẻ
            $isOwner = $note->user_id === Auth::id();
            $canEdit = $note->shares()
                ->where('user_id', Auth::id())
                ->where('permission', 'edit')
                ->exists();

            if (!$isOwner && !$canEdit) {
                return response()->json([
                    'error' => 'Bạn chỉ có quyền xem, không được chỉnh sửa ghi chú này'
                ], 403);
            }

            $note->update([
                'title' => $validated['title'],
                'content' => $validated['content'] ?? '',
                'color' => $color,
            ]);
        } else {
            // Tạo mới luôn thuộc về user đang đăng nhập
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

    public function setPassword(Request $request, Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:4'],
        ]);

        $note->password_hash = Hash::make($validated['password']);
        $note->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã thiết lập mật khẩu cho ghi chú',
        ]);
    }

    /**
     * Gỡ bỏ mật khẩu bảo vệ của ghi chú.
     */
    public function removePassword(Request $request, Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['current_password'], $note->password_hash)) {
            return response()->json(['error' => 'Mật khẩu hiện tại không chính xác'], 422);
        }

        $note->password_hash = null;
        $note->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã gỡ bỏ mật khẩu bảo vệ',
        ]);
    }

    /**
     * Xác thực mật khẩu để mở khóa xem và sửa ghi chú.
     */
    public function unlockNote(Request $request, Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['password'], $note->password_hash)) {
            return response()->json(['error' => 'Mật khẩu không chính xác'], 422);
        }

        // Trả về dữ liệu đầy đủ sau khi mở khóa
        return response()->json([
            'success' => true,
            'note' => $note->fresh(['labels', 'attachments']),
        ]);
    }
}