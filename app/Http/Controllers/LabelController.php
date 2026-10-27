<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class LabelController extends Controller
{
    /**
     * Lấy toàn bộ danh sách nhãn của người dùng kèm số lượng ghi chú.
     */
    public function index(): JsonResponse
    {
        $labels = Label::where('user_id', Auth::id())
            ->withCount('notes')
            ->orderBy('name')
            ->get();

        return response()->json(['labels' => $labels]);
    }

    /**
     * Tạo nhãn mới.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $label = Label::create([
            'user_id' => Auth::id(),
            'name' => trim($validated['name']),
        ]);

        return response()->json([
            'success' => true,
            'label' => $label,
            'message' => 'Đã tạo nhãn mới',
        ]);
    }

    /**
     * Đổi tên nhãn (Tất cả ghi chú gắn nhãn này tự động phản ánh tên mới).
     */
    public function update(Request $request, Label $label): JsonResponse
    {
        if ($label->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $label->update([
            'name' => trim($validated['name']),
        ]);

        return response()->json([
            'success' => true,
            'label' => $label,
            'message' => 'Đã cập nhật tên nhãn',
        ]);
    }

    /**
     * Xóa nhãn (Chỉ xóa liên kết note_label, không xóa ghi chú).
     */
    public function destroy(Label $label): JsonResponse
    {
        if ($label->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Quan hệ onDelete('cascade') ở bảng note_label sẽ tự động dọn liên kết
        $label->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa nhãn thành công',
        ]);
    }
}