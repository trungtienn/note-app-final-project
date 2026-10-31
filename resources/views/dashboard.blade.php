<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Ghi chú của tôi') }}
            </h2>

            <!-- Công cụ Live Search & Toggle View -->
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <!-- Live Search: không có nút bấm, quét title + content, debounce 300ms -->
                <div class="relative w-full sm:w-72">
                    <input type="text" id="live-search" placeholder="Tìm kiếm ghi chú (tiêu đề, nội dung)..."
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500 pl-9 pr-4 py-2 shadow-sm">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Chuyển đổi Grid View / List View (Mặc định là Grid view theo đề) -->
                <div class="flex bg-gray-200 dark:bg-gray-700 p-1 rounded-lg">
                    <button id="btn-grid-view" onclick="setViewMode('grid')" class="p-1.5 rounded bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 shadow-sm" title="Dạng lưới (Grid)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    </button>
                    <button id="btn-list-view" onclick="setViewMode('list')" class="p-1.5 rounded text-gray-500 dark:text-gray-400 hover:text-gray-700" title="Dạng danh sách (List)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- THANH LỌC GHI CHÚ THEO NHÃN (LABEL FILTER BAR) -->
        <div class="mb-6 flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex-shrink-0">Nhãn:</span>
            <div id="labels-filter-bar" class="flex items-center gap-1.5 flex-nowrap">
                <button onclick="filterByLabel(null)" id="filter-label-all" class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-600 text-white shadow-sm flex-shrink-0">
                    Tất cả
                </button>
            </div>
            <button onclick="openManageLabelsModal()" class="px-2.5 py-1 text-xs font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 border border-dashed border-gray-300 dark:border-gray-600 rounded-full flex items-center gap-1 flex-shrink-0" title="Quản lý nhãn">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Quản lý nhãn
            </button>
        </div>
        <!-- Nút mở Editor tạo ghi chú mới -->
        <div class="mb-6 flex justify-between items-center">
            <button onclick="openNoteModal(null)" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tạo ghi chú mới
            </button>
            <span id="save-status-indicator" class="text-xs text-gray-500 dark:text-gray-400 italic"></span>
        </div>

        <!-- Vùng hiển thị danh sách Note (Grid mặc định) -->
        <div id="notes-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <!-- Dynamic Notes Content -->
        </div>

        <!-- Empty State khi không có note -->
        <div id="empty-state" class="hidden text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-gray-500 dark:text-gray-400 font-medium">Chưa có ghi chú nào. Hãy tạo ghi chú đầu tiên của bạn!</p>
        </div>

        <!-- KHU VỰC GHI CHÚ ĐƯỢC CHIA SẺ VỚI TÔI -->
        <div id="shared-notes-wrapper" class="mt-10 hidden border-t border-gray-200 dark:border-gray-700 pt-8">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Ghi chú được chia sẻ với tôi
            </h3>
            <div id="shared-notes-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"></div>
        </div>
    </div>

    <!-- SINGLE INTERFACE MODAL: DÙNG CHUNG CHO CẢ TẠO VÀ SỬA (BẮT BUỘC) -->
    <div id="note-modal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div id="modal-box" class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full p-6 transition-all border border-gray-200 dark:border-gray-700">
            <input type="hidden" id="editor-note-id">

            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-2">
                    <span id="modal-title-status" class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Ghi chú</span>
                    <span id="modal-auto-save-status" class="text-xs text-gray-400 italic"></span>
                </div>
                <!-- Nút đóng (tự lưu trước khi đóng) -->
                <button onclick="closeNoteModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Trường Title bắt buộc -->
            <div class="mb-3">
                <input type="text" id="editor-title" placeholder="Tiêu đề..." oninput="triggerAutoSave()"
                    class="w-full text-lg font-bold border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent px-0 py-2 focus:ring-0 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400">
            </div>

            <!-- Trường Content bắt buộc -->
            <div class="mb-4">
                <textarea id="editor-content" rows="8" placeholder="Nhập nội dung ghi chú..." oninput="triggerAutoSave()"
                    class="w-full border-0 bg-transparent px-0 py-2 focus:ring-0 text-gray-800 dark:text-gray-200 placeholder-gray-400 resize-none"></textarea>
            </div>

            <!-- KHU VỰC CHỌN NHÃN (LABELS) TRONG MODAL -->
            <div id="note-labels-section" class="mb-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nhãn ghi chú</span>
                </div>
                <div id="note-labels-chips" class="flex flex-wrap gap-1.5">
                    <!-- Checkbox dạng chip nhãn -->
                </div>
            </div>

            <!-- KHU VỰC TỆP ĐÍNH KÈM (ATTACHMENTS) -->
            <div id="attachments-section" class="mb-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tệp đính kèm (Ảnh / Video / File)</span>
                    <label id="btn-add-attachment" class="cursor-pointer inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                        <span>Thêm tệp</span>
                        <input type="file" id="attachment-file-input" multiple class="hidden" onchange="handleUploadAttachments(event)">
                    </label>
                </div>
                <!-- Vùng hiển thị xem trước ảnh, video và file -->
                <div id="attachments-list" class="grid grid-cols-2 sm:grid-cols-3 gap-2"></div>
            </div>

            <!-- Thanh trạng thái chân Modal -->
            <div class="flex justify-between items-center pt-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500">
                <div class="flex items-center gap-4">
                    <span>Hệ thống tự động lưu khi dừng gõ</span>
                    <!-- Nút đặt / gỡ mật khẩu -->
                    <button type="button" id="btn-toggle-note-lock" onclick="handleNoteSecurity()" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Đặt mật khẩu</span>
                    </button>
                    <button type="button" id="btn-share-note" onclick="openShareModal()" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        <span>Chia sẻ</span>
                    </button>
                </div>
                <button type="button" onclick="closeNoteModal()" class="px-4 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded text-gray-700 dark:text-gray-200 font-medium">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL QUẢN LÝ NHÃN (CRUD LABELS: THÊM, ĐỔI TÊN, XÓA) -->
     <div id="manage-labels-modal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-base text-gray-900 dark:text-gray-100">Quản lý nhãn</h3>
                <button onclick="closeManageLabelsModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Form tạo nhãn mới -->
            <div class="flex gap-2 mb-4">
                <input type="text" id="new-label-name" placeholder="Tên nhãn mới..." class="flex-1 text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 px-3 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                <button onclick="createNewlabel()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow">Thêm</button>
            </div>

            <!-- Danh sách nhãn hiện có (Sửa/Xóa) -->
            <div id="manage-labels-list" class="max-h-60 overflow-y-auto space-y-2 pr-1"></div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 text-right">
                <button onclick="closeManageLabelsModal()" class="px-4 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded text-xs font-medium">Đóng</button>
            </div>
        </div>
    </div>

    <!-- CONFIRMATION DIALOG -->
    <div id="delete-modal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full shadow-xl border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Xác nhận xóa ghi chú?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Ghi chú này sẽ bị xóa hoàn toàn khỏi hệ thống. Bạn có chắc chắn muốn tiếp tục?</p>
            <div class="mt-6 flex justify-end gap-3">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200">Hủy</button>
                <button id="btn-confirm-delete" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 shadow">Xóa vĩnh viễn</button>
            </div>
        </div>
    </div>

    <div id="unlock-modal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-sm w-full shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-3 text-amber-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Ghi chú được bảo vệ</h3>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Vui lòng nhập mật khẩu để mở khóa và xem nội dung ghi chú này.</p>
            
            <input type="password" id="unlock-password-input" placeholder="Nhập mật khẩu ghi chú..." 
                   class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 mb-2 focus:ring-indigo-500 focus:border-indigo-500">
            <p id="unlock-error-msg" class="text-xs text-red-500 font-medium mb-3 hidden"></p>

            <div class="flex justify-end gap-2">
                <button onclick="closeUnlockModal()" class="px-3.5 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">Hủy</button>
                <button onclick="submitUnlockNote()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow">Mở khóa</button>
            </div>
        </div>
    </div>

    <!-- MODAL QUẢN LÝ CHIA SẺ GHI CHÚ -->
    <div id="share-modal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-base text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    Chia sẻ ghi chú
                </h3>
                <button onclick="closeShareModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Form thêm người nhận -->
            <div class="flex gap-2 mb-4">
                <input type="email" id="share-email-input" placeholder="Nhập email người nhận..." class="flex-1 text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 px-3 py-2">
                <select id="share-permission-select" class="text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 py-2">
                    <option value="view">Chỉ xem</option>
                    <option value="edit">Được sửa</option>
                </select>
                <button onclick="submitShareNote()" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow">Gửi</button>
            </div>
            <p id="share-error-msg" class="text-xs text-red-500 mb-3 hidden"></p>

            <!-- Danh sách người đã được chia sẻ -->
            <div class="text-xs font-semibold uppercase text-gray-400 mb-2">Đang chia sẻ với:</div>
            <div id="shared-users-list" class="max-h-48 overflow-y-auto space-y-2"></div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 text-right">
                <button onclick="closeShareModal()" class="px-4 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded text-xs font-medium">Đóng</button>
            </div>
        </div>
    </div>
    <!-- JAVASCRIPT LOGIC CHO CÁC TÍNH NĂNG -->
    <script>
        let currentView = 'grid'; // 'grid' hoặc 'list'
        let autoSaveTimer = null;
        let noteToDeleteId = null;
        let allNotes = [];
        let allLabels = [];
        let selectedLabelFilter = null;
        let allSharedNotes = [];
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Khởi chạy khi load trang
        document.addEventListener('DOMContentLoaded', () => {
            loadLabels();
            loadNotes();
            setupLiveSearch();
        });

        // 1. Tải và lọc ghi chú (hỗ trợ search + label_id)
        async function loadNotes(searchTerm = '') {
            try {
                let url = '/api/notes?';
                if (searchTerm) url += `search=${encodeURIComponent(searchTerm)}&`;
                if (selectedLabelFilter) url += `label_id=${selectedLabelFilter}&`;

                const res = await fetch(url);
                const data = await res.json();
                allNotes = data.notes || [];
                allSharedNotes = data.shared_notes || [];
                renderNotes();
                renderSharedNotes();
            } catch (err) {
                console.error('Lỗi khi tải notes:', err);
            }
        }

        // 2. Chuyển đổi Grid View / List View
        function setViewMode(mode) {
            currentView = mode;
            const container = document.getElementById('notes-container');
            const btnGrid = document.getElementById('btn-grid-view');
            const btnList = document.getElementById('btn-list-view');

            if (mode === 'grid') {
                container.className = 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4';
                btnGrid.classList.add('bg-white', 'dark:bg-gray-800', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-sm');
                btnList.classList.remove('bg-white', 'dark:bg-gray-800', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-sm');
            } else {
                container.className = 'flex flex-col gap-3 max-w-4xl mx-auto';
                btnList.classList.add('bg-white', 'dark:bg-gray-800', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-sm');
                btnGrid.classList.remove('bg-white', 'dark:bg-gray-800', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-sm');
            }
            renderNotes();
        }

        // 3. Render danh sách thẻ ghi chú (kèm nhãn và các icon Pin, Lock)
        function renderNotes() {
            const container = document.getElementById('notes-container');
            const emptyState = document.getElementById('empty-state');
            container.innerHTML = '';

            if (allNotes.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }
            emptyState.classList.add('hidden');

            allNotes.forEach(note => {
                const card = document.createElement('div');
                const isList = currentView === 'list';
                card.className = `p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition cursor-pointer flex ${isList ? 'justify-between items-center' : 'flex-col justify-between min-h-48'}`;
                
                card.onclick = (e) => {
                    if (e.target.closest('button')) return;
                    if (note.has_password) {
                        openUnlockModal(note.id);
                    } else {
                        const freshNote = allNotes.find(n => n.id === note.id) || note;
                        openNoteModal(freshNote);
                    }
                };

                let statusIcons = '';
                if (note.is_pinned) {
                    statusIcons += `<span title="Ghi chú đã ghim" class="text-amber-500"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg></span>`;
                }
                if (note.password_hash) {
                    statusIcons += `<span title="Có mật khẩu bảo vệ" class="text-red-500"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg></span>`;
                }

                // Hiển thị nhãn trên thẻ ghi chú
                let labelsHtml = '';
                if (note.labels && note.labels.length > 0) {
                    labelsHtml = `<div class="flex flex-wrap gap-1 mt-1 mb-2">${note.labels.map(l => `<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300">${escapeHtml(l.name)}</span>`).join('')}</div>`;
                }

                card.innerHTML = `
                    <div class="${isList ? 'flex-1 pr-4' : 'overflow-hidden'}">
                        <div class="flex items-center gap-2 mb-1">
                            ${statusIcons}
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 truncate">${escapeHtml(note.title)}</h3>
                        </div>
                        ${labelsHtml}
                        <p class="text-sm text-gray-600 dark:text-gray-400 ${isList ? 'truncate' : 'line-clamp-3'}">${escapeHtml(note.content || 'Không có nội dung')}</p>
                    </div>
                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
                        <span>${new Date(note.updated_at).toLocaleDateString('vi-VN')}</span>
                        <div class="flex items-center gap-2">
                            <button onclick="togglePinNote(${note.id})" title="${note.is_pinned ? 'Bỏ ghim' : 'Ghim lên đầu'}" class="p-1 hover:text-amber-500">
                                <svg class="w-4 h-4" fill="${note.is_pinned ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                            </button>
                            <button onclick="promptDelete(${note.id})" title="Xóa ghi chú" class="p-1 hover:text-red-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        // 4. Live Search: Debounce 300ms
        function setupLiveSearch() {
            const searchInput = document.getElementById('live-search');
            let searchTimer = null;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    loadNotes(e.target.value.trim());
                }, 300);
            });
        }

        // 5. Single Interface Modal: Mở chung cho cả Tạo mới và Sửa
        function openNoteModal(note = null) {
            const modal = document.getElementById('note-modal');
            const idField = document.getElementById('editor-note-id');
            const titleField = document.getElementById('editor-title');
            const contentField = document.getElementById('editor-content');
            const titleStatus = document.getElementById('modal-title-status');
            const saveStatus = document.getElementById('modal-auto-save-status');
            const attachmentsList = document.getElementById('attachments-list');
            const btnAddAttachment = document.getElementById('btn-add-attachment');
            const btnShare = document.getElementById('btn-share-note');

            saveStatus.innerText = '';
            attachmentsList.innerHTML = '';

            // Kiểm tra quyền chỉ xem (view-only)
            const isReadOnly = Boolean(note && note.permission === 'view');
            titleField.readOnly = isReadOnly;
            contentField.readOnly = isReadOnly;

            if (note) {
                idField.value = note.id;
                titleField.value = note.title;
                contentField.value = note.content || '';
                
                if (isReadOnly) {
                    titleStatus.innerText = 'Xem ghi chú (Chỉ đọc)';
                    btnAddAttachment.classList.add('opacity-50', 'pointer-events-none');
                } else {
                    titleStatus.innerText = (note.is_owner === false) ? 'Cùng chỉnh sửa ghi chú' : 'Chỉnh sửa ghi chú';
                    btnAddAttachment.classList.remove('opacity-50', 'pointer-events-none');
                }
                
                if (note.attachments && note.attachments.length > 0) {
                    note.attachments.forEach(att => renderAttachmentItem(att));
                }
            } else {
                idField.value = '';
                titleField.value = '';
                contentField.value = '';
                titleStatus.innerText = 'Tạo ghi chú mới';
                btnAddAttachment.classList.add('opacity-50', 'pointer-events-none');
            }

            // Chỉ chủ sở hữu ghi chú mới thấy nút chia sẻ và nút quản lý mật khẩu
            const isOwner = !note || !note.id || note.is_owner !== false;
            if (btnShare) {
                if (note && note.id && isOwner) {
                    btnShare.classList.remove('hidden');
                } else {
                    btnShare.classList.add('hidden');
                }
            }

            renderNoteLabelChips(note);
            updateSecurityButton(isOwner ? note : null);
            modal.classList.remove('hidden');
            if (!isReadOnly) {
                titleField.focus();
            }
        }

        function closeNoteModal() {
            document.getElementById('note-modal').classList.add('hidden');
            loadNotes();
        }

        // 6. Cơ chế Auto-save (ngừng gõ 600ms tự lưu)
        function triggerAutoSave() {
            const saveStatus = document.getElementById('modal-auto-save-status');
            saveStatus.innerText = 'Đang lưu ngầm...';

            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                performAutoSave();
            }, 600);
        }

        async function performAutoSave() {
            const id = document.getElementById('editor-note-id').value;
            const title = document.getElementById('editor-title').value.trim();
            const content = document.getElementById('editor-content').value;
            const saveStatus = document.getElementById('modal-auto-save-status');

            if (!title) {
                saveStatus.innerText = 'Cần nhập tiêu đề để tự lưu';
                return;
            }

            try {
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                const token = metaToken ? metaToken.getAttribute('content') : '';

                const res = await fetch('/api/notes/auto-save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({ 
                        id: id ? parseInt(id) : null, 
                        title: title, 
                        content: content 
                    }),
                });

                if (!res.ok) {
                    const errData = await res.json().catch(() => ({}));
                    console.error('Lỗi phản hồi API:', res.status, errData);
                    saveStatus.innerText = `Lỗi (${res.status}): Không thể lưu`;
                    return;
                }

                const data = await res.json();
                if (data.success && data.note) {
                    document.getElementById('editor-note-id').value = data.note.id;
                    updateSecurityButton(data.note);
                    document.getElementById('btn-add-attachment').classList.remove('opacity-50', 'pointer-events-none');
                    saveStatus.innerText = '✓ Đã tự động lưu';
                } else {
                    saveStatus.innerText = 'Lưu thất bại';
                }
            } catch (err) {
                console.error('Lỗi mạng:', err);
                saveStatus.innerText = 'Lỗi kết nối';
            }
        }

        // 7. Ghim ghi chú
        async function togglePinNote(noteId) {
            try {
                await fetch(`/api/notes/${noteId}/toggle-pin`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                });
                loadNotes();
            } catch (err) {
                console.error(err);
            }
        }

        // 8. Hộp thoại xác nhận xóa
        function promptDelete(noteId) {
            noteToDeleteId = noteId;
            document.getElementById('delete-modal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            noteToDeleteId = null;
            document.getElementById('delete-modal').classList.add('hidden');
        }

        document.getElementById('btn-confirm-delete').onclick = async () => {
            if (!noteToDeleteId) return;
            try {
                await fetch(`/api/notes/${noteToDeleteId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                });
                closeDeleteModal();
                loadNotes();
                loadLabels();
            } catch (err) {
                console.error('Lỗi xóa note:', err);
            }
        };

        // 9. Xử lý Tệp đính kèm
        async function handleUploadAttachments(event) {
            const files = event.target.files;
            const noteId = document.getElementById('editor-note-id').value;

            if (!noteId) {
                alert('Vui lòng nhập tiêu đề ghi chú để hệ thống tự lưu trước khi tải tệp!');
                return;
            }
            if (files.length === 0) return;

            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }

            const saveStatus = document.getElementById('modal-auto-save-status');
            saveStatus.innerText = 'Đang tải tệp lên...';

            try {
                const res = await fetch(`/api/notes/${noteId}/attachments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await res.json();
                if (data.success) {
                    saveStatus.innerText = '✓ Đã tải tệp lên';
                    data.attachments.forEach(att => renderAttachmentItem(att));
                    loadNotes();
                } else {
                    saveStatus.innerText = 'Lỗi tải tệp';
                }
            } catch (err) {
                console.error(err);
                saveStatus.innerText = 'Lỗi kết nối tải tệp';
            } finally {
                event.target.value = '';
            }
        }

        function renderAttachmentItem(att) {
            const container = document.getElementById('attachments-list');
            const item = document.createElement('div');
            item.id = `attachment-${att.id}`;
            item.className = 'relative group border border-gray-200 dark:border-gray-700 rounded-lg p-2 bg-gray-50 dark:bg-gray-900 flex flex-col justify-between';

            const fileUrl = att.url || `/storage/${att.file_path}`;
            const fileName = att.name || att.file_name;
            const fileType = att.type || att.file_type;

            let previewHtml = '';
            if (fileType === 'image') {
                previewHtml = `<img src="${fileUrl}" class="h-20 w-full object-cover rounded mb-1" alt="${fileName}">`;
            } else if (fileType === 'video') {
                previewHtml = `<video src="${fileUrl}" class="h-20 w-full object-cover rounded mb-1" controls></video>`;
            } else {
                previewHtml = `
                    <div class="h-20 flex items-center justify-center bg-gray-200 dark:bg-gray-800 rounded mb-1">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>`;
            }

            item.innerHTML = `
                ${previewHtml}
                <div class="flex items-center justify-between gap-1 text-xs">
                    <a href="${fileUrl}" target="_blank" class="truncate text-indigo-600 dark:text-indigo-400 hover:underline flex-1" title="${fileName}">${fileName}</a>
                    <button type="button" onclick="deleteAttachmentItem(${att.id})" class="text-red-500 hover:text-red-700 p-0.5" title="Xóa tệp">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            `;
            container.appendChild(item);
        }

        async function deleteAttachmentItem(attachmentId) {
            if (!confirm('Bạn có chắc muốn xóa tệp này?')) return;
            try {
                const res = await fetch(`/api/attachments/${attachmentId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.success) {
                    const el = document.getElementById(`attachment-${attachmentId}`);
                    if (el) el.remove();
                    loadNotes();
                }
            } catch (err) {
                console.error('Lỗi xóa file:', err);
            }
        }

        // 10. Quản lý Nhãn (Labels)
        async function loadLabels() {
            try {
                const res = await fetch('/api/labels');
                const data = await res.json();
                allLabels = data.labels;
                renderLabelFilterBar();
            } catch (err) {
                console.error('Lỗi tải nhãn:', err);
            }
        }

        function renderLabelFilterBar() {
            const bar = document.getElementById('labels-filter-bar');
            bar.innerHTML = `
                <button onclick="filterByLabel(null)" id="filter-label-all" class="px-3 py-1 rounded-full text-xs font-semibold ${selectedLabelFilter === null ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'} flex-shrink-0">
                    Tất cả
                </button>
            `;

            allLabels.forEach(l => {
                const isSelected = selectedLabelFilter === l.id;
                const btn = document.createElement('button');
                btn.className = `px-3 py-1 rounded-full text-xs font-semibold flex-shrink-0 transition ${isSelected ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'}`;
                btn.innerText = `${l.name} (${l.notes_count})`;
                btn.onclick = () => filterByLabel(l.id);
                bar.appendChild(btn);
            });
        }

        function filterByLabel(labelId) {
            selectedLabelFilter = labelId;
            renderLabelFilterBar();
            const searchVal = document.getElementById('live-search').value.trim();
            loadNotes(searchVal);
        }

        function renderNoteLabelChips(currentNote = null) {
            const container = document.getElementById('note-labels-chips');
            container.innerHTML = '';

            if (allLabels.length === 0) {
                container.innerHTML = `<span class="text-xs text-gray-400 italic">Chưa có nhãn nào. Hãy tạo nhãn ở nút "Quản lý nhãn".</span>`;
                return;
            }

            const currentNoteLabelIds = (currentNote && currentNote.labels) ? currentNote.labels.map(l => l.id) : [];

            allLabels.forEach(label => {
                const isChecked = currentNoteLabelIds.includes(label.id);
                const chip = document.createElement('label');
                chip.className = `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs cursor-pointer border transition ${isChecked ? 'bg-indigo-50 border-indigo-400 text-indigo-700 dark:bg-indigo-900/40 dark:border-indigo-500 dark:text-indigo-300 font-semibold' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'}`;
                chip.innerHTML = `
                    <input type="checkbox" value="${label.id}" ${isChecked ? 'checked' : ''} onchange="toggleNoteLabel(${label.id}, this.checked)" class="rounded text-indigo-600 focus:ring-0 w-3.5 h-3.5 border-gray-300">
                    <span>${escapeHtml(label.name)}</span>
                `;
                container.appendChild(chip);
            });
        }

        async function toggleNoteLabel(labelId, isChecked) {
            const noteId = document.getElementById('editor-note-id').value;
            if (!noteId) {
                alert('Vui lòng nhập tiêu đề ghi chú trước khi chọn nhãn!');
                return;
            }

            const currentNote = allNotes.find(n => n.id == noteId);
            let labelIds = currentNote && currentNote.labels ? currentNote.labels.map(l => l.id) : [];

            if (isChecked) {
                if (!labelIds.includes(labelId)) labelIds.push(labelId);
            } else {
                labelIds = labelIds.filter(id => id !== labelId);
            }

            try {
                const res = await fetch(`/api/notes/${noteId}/sync-labels`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ label_ids: labelIds }),
                });
                const data = await res.json();
                if (data.success) {
                    await loadLabels();
                    await loadNotes();
                }
            } catch (err) {
                console.error(err);
            }
        }

        function openManageLabelsModal() {
            document.getElementById('manage-labels-modal').classList.remove('hidden');
            renderManageLabelsList();
        }

        function closeManageLabelsModal() {
            document.getElementById('manage-labels-modal').classList.add('hidden');
        }

        function renderManageLabelsList() {
            const list = document.getElementById('manage-labels-list');
            list.innerHTML = '';

            if (allLabels.length === 0) {
                list.innerHTML = `<p class="text-xs text-gray-400 italic">Chưa có nhãn nào.</p>`;
                return;
            }

            allLabels.forEach(l => {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between gap-2 p-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700/50';
                row.innerHTML = `
                    <input type="text" id="label-input-${l.id}" value="${escapeHtml(l.name)}" class="text-xs flex-1 rounded border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 py-1 px-2">
                    <button onclick="updateLabelName(${l.id})" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Lưu</button>
                    <button onclick="deleteLabel(${l.id})" class="text-xs text-red-500 hover:text-red-700">Xóa</button>
                `;
                list.appendChild(row);
            });
        }

        async function createNewlabel() {
            const input = document.getElementById('new-label-name');
            const name = input.value.trim();
            if (!name) return;

            try {
                const res = await fetch('/api/labels', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ name }),
                });
                const data = await res.json();
                if (data.success) {
                    input.value = '';
                    await loadLabels();
                    renderManageLabelsList();
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function updateLabelName(labelId) {
            const name = document.getElementById(`label-input-${labelId}`).value.trim();
            if (!name) return;

            try {
                const res = await fetch(`/api/labels/${labelId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ name }),
                });
                const data = await res.json();
                if (data.success) {
                    await loadLabels();
                    loadNotes();
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function deleteLabel(labelId) {
            if (!confirm('Xóa nhãn này? (Các ghi chú gắn nhãn này sẽ không bị xóa)')) return;

            try {
                const res = await fetch(`/api/labels/${labelId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                });
                const data = await res.json();
                if (data.success) {
                    if (selectedLabelFilter === labelId) selectedLabelFilter = null;
                    await loadLabels();
                    loadNotes();
                    renderManageLabelsList();
                }
            } catch (err) {
                console.error(err);
            }
        }
        // Xử lý khoá và mở khoá ghi chú
        let pendingUnlockNoteId = null;

        function openUnlockModal(noteId) {
            pendingUnlockNoteId = noteId;
            document.getElementById('unlock-password-input').value = '';
            document.getElementById('unlock-error-msg').classList.add('hidden');
            document.getElementById('unlock-modal').classList.remove('hidden');
            document.getElementById('unlock-password-input').focus();
        }

        function closeUnlockModal() {
            pendingUnlockNoteId = null;
            document.getElementById('unlock-modal').classList.add('hidden');
        }

        async function submitUnlockNote() {
            const password = document.getElementById('unlock-password-input').value;
            const errorMsg = document.getElementById('unlock-error-msg');

            if (!password) {
                errorMsg.innerText = 'Vui lòng nhập mật khẩu';
                errorMsg.classList.remove('hidden');
                return;
            }

            try {
                const res = await fetch(`/api/notes/${pendingUnlockNoteId}/unlock`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ password }),
                });

                const data = await res.json();
                if (!res.ok) {
                    errorMsg.innerText = data.error || 'Mật khẩu không chính xác';
                    errorMsg.classList.remove('hidden');
                    return;
                }

                closeUnlockModal();
                // Mở modal với dữ liệu đã được server giải mã
                openNoteModal(data.note);
            } catch (err) {
                errorMsg.innerText = 'Lỗi kết nối';
                errorMsg.classList.remove('hidden');
            }
        }

        // Cập nhật nút khóa trong openNoteModal(note)
        function updateSecurityButton(note) {
            const btn = document.getElementById('btn-toggle-note-lock');
            if (!note || !note.id) {
                btn.classList.add('hidden');
                return;
            }
            btn.classList.remove('hidden');
            if (note.has_password || note.password_hash) {
                btn.innerHTML = `<svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> Gỡ mật khẩu`;
            } else {
                btn.innerHTML = `<svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Đặt mật khẩu`;
            }
        }

        async function handleNoteSecurity() {
            const noteId = document.getElementById('editor-note-id').value;
            const currentNote = allNotes.find(n => n.id == noteId);
            const isLocked = currentNote && (currentNote.has_password || currentNote.password_hash);

            if (isLocked) {
                const currentPass = prompt('Nhập mật khẩu hiện tại để gỡ bỏ bảo vệ:');
                if (!currentPass) return;

                const res = await fetch(`/api/notes/${noteId}/remove-password`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ current_password: currentPass }),
                });
                const data = await res.json();
                if (data.success) {
                    alert('Đã gỡ mật khẩu thành công!');
                    currentNote.has_password = false;
                    delete currentNote.password_hash;
                    updateSecurityButton(currentNote);
                    loadNotes();
                } else {
                    alert(data.error || 'Mật khẩu không đúng!');
                }
            } else {
                const newPass = prompt('Nhập mật khẩu mới cho ghi chú này (tối thiểu 4 ký tự):');
                if (!newPass) return;

                const res = await fetch(`/api/notes/${noteId}/set-password`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ password: newPass }),
                });
                const data = await res.json();
                if (data.success) {
                    alert('Đã đặt mật khẩu thành công!');
                    if (currentNote) currentNote.has_password = true;
                    updateSecurityButton({ has_password: true });
                    loadNotes();
                } else {
                    alert(data.error || 'Không thể đặt mật khẩu!');
                }
            }
        }

        function renderSharedNotes() {
            const wrapper = document.getElementById('shared-notes-wrapper');
            const container = document.getElementById('shared-notes-container');
            container.innerHTML = '';

            if (allSharedNotes.length === 0) {
                wrapper.classList.add('hidden');
                return;
            }
            wrapper.classList.remove('hidden');

            allSharedNotes.forEach(note => {
                const card = document.createElement('div');
                card.className = 'p-4 rounded-xl border border-indigo-200 dark:border-indigo-900/50 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition cursor-pointer flex flex-col justify-between min-h-48';
                
                card.onclick = () => {
                    openNoteModal(note);
                };

                const permBadge = note.permission === 'edit' 
                    ? '<span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-semibold">Được sửa</span>'
                    : '<span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-semibold">Chỉ xem</span>';

                const ownerName = note.user ? escapeHtml(note.user.display_name || note.user.email) : 'Người khác';

                card.innerHTML = `
                    <div class="overflow-hidden">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 truncate flex-1">${escapeHtml(note.title)}</h3>
                            ${permBadge}
                        </div>
                        <p class="text-xs text-indigo-500 mb-2 italic">Từ: ${ownerName}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">${escapeHtml(note.content || 'Không có nội dung')}</p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
                        <span>Cập nhật: ${new Date(note.updated_at).toLocaleDateString('vi-VN')}</span>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        async function openShareModal() {
            const noteId = document.getElementById('editor-note-id').value;
            if (!noteId) return;

            document.getElementById('share-email-input').value = '';
            document.getElementById('share-error-msg').classList.add('hidden');
            document.getElementById('share-modal').classList.remove('hidden');
            await loadSharedUsers(noteId);
        }

        function closeShareModal() {
            document.getElementById('share-modal').classList.add('hidden');
        }

        async function loadSharedUsers(noteId) {
            try {
                const res = await fetch(`/api/notes/${noteId}/shares`);
                const data = await res.json();
                const list = document.getElementById('shared-users-list');
                list.innerHTML = '';

                if (!data.shares || data.shares.length === 0) {
                    list.innerHTML = '<p class="text-xs text-gray-400 italic">Chưa chia sẻ với ai.</p>';
                    return;
                }

                data.shares.forEach(s => {
                    const row = document.createElement('div');
                    row.className = 'flex items-center justify-between p-2 rounded bg-gray-50 dark:bg-gray-700/50 text-xs';
                    row.innerHTML = `
                        <div>
                            <span class="font-medium text-gray-800 dark:text-gray-200">${escapeHtml(s.user.display_name || s.user.email)}</span>
                            <span class="text-[10px] text-gray-400">(${s.permission === 'edit' ? 'Được sửa' : 'Chỉ xem'})</span>
                        </div>
                        <button onclick="revokeShare(${s.id})" class="text-red-500 hover:text-red-700 font-medium">Hủy</button>
                    `;
                    list.appendChild(row);
                });
            } catch (err) {
                console.error(err);
            }
        }

        async function submitShareNote() {
            const noteId = document.getElementById('editor-note-id').value;
            const email = document.getElementById('share-email-input').value.trim();
            const permission = document.getElementById('share-permission-select').value;
            const errorMsg = document.getElementById('share-error-msg');

            if (!email) {
                errorMsg.innerText = 'Vui lòng nhập email';
                errorMsg.classList.remove('hidden');
                return;
            }

            try {
                const res = await fetch(`/api/notes/${noteId}/share`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ email, permission }),
                });

                const data = await res.json();
                if (!res.ok) {
                    errorMsg.innerText = data.error || 'Lỗi khi chia sẻ';
                    errorMsg.classList.remove('hidden');
                    return;
                }

                errorMsg.classList.add('hidden');
                document.getElementById('share-email-input').value = '';
                await loadSharedUsers(noteId);
                loadNotes();
            } catch (err) {
                errorMsg.innerText = 'Lỗi kết nối';
                errorMsg.classList.remove('hidden');
            }
        }

        async function revokeShare(shareId) {
            const noteId = document.getElementById('editor-note-id').value;
            try {
                await fetch(`/api/shares/${shareId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                });
                await loadSharedUsers(noteId);
                loadNotes();
            } catch (err) {
                console.error(err);
            }
        }

        function escapeHtml(str) {
            return (str || '').replace(/[&<>'"]/g, 
                tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
            );
        }
    </script>
</x-app-layout>