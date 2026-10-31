<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use App\Models\NoteShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ShareController extends Controller
{
    /**
     * Lấy danh sách những người đã được chia sẻ ghi chú này
     */
    public function index(Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Chỉ chủ sở hữu mới xem được danh sách chia sẻ'], 403);
        }

        $shares = $note->shares()->with('user:id,email,display_name,avatar')->get();

        return response()->json([
            'success' => true,
            'shares' => $shares,
        ]);
    }

    /**
     * Chia sẻ ghi chú cho người dùng khác qua Email
     */
    public function share(Request $request, Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Chỉ chủ sở hữu mới có quyền chia sẻ ghi chú'], 403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'permission' => ['required', 'in:view,edit'],
        ]);

        $recipient = User::where('email', $validated['email'])->first();

        if (!$recipient) {
            return response()->json(['error' => 'Không tìm thấy người dùng với email này trong hệ thống'], 404);
        }

        if ($recipient->id === Auth::id()) {
            return response()->json(['error' => 'Bạn không thể tự chia sẻ ghi chú cho chính mình'], 422);
        }

        // Tạo hoặc cập nhật quyền nếu đã chia sẻ trước đó
        $share = NoteShare::updateOrCreate(
            ['note_id' => $note->id, 'user_id' => $recipient->id],
            ['permission' => $validated['permission']]
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã chia sẻ ghi chú thành công',
            'share' => $share->load('user:id,email,display_name'),
        ]);
    }

    /**
     * Hủy quyền chia sẻ của một người dùng
     */
    public function revoke(NoteShare $share): JsonResponse
    {
        $note = $share->note;
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $share->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy chia sẻ',
        ]);
    }
}