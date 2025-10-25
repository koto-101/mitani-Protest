@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endsection

@section('content')
<div class="chat-container d-flex">

    {{-- 左サイドバー --}}
    <div class="chat-sidebar p-3 border-end">
        <h5>その他の取引</h5>
        <ul class="list-unstyled">
            @foreach($otherChatItems as $other)
                <li class="mb-2">
                    <a href="{{ route('chat.show', ['chatRoom' => $other->id]) }}" class="text-decoration-none">
                        {{ $other->item->title ?? 'タイトルなし' }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    {{-- チャットメイン --}}
    <div class="chat-main flex-grow-1 p-4">
        @php
            $transaction = $chatRoom->transaction;
            $isBuyer = auth()->id() === $chatRoom->buyer_id;
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <img src="{{ asset('storage/' . optional($item->item_images->first())->image_path) }}" alt="商品画像" class="me-3" width="60">
                <div>
                    <h5 class="mb-1">{{ $partner->name }}さんとの取引画面</h5>
                    <p class="mb-0">{{ $item->title }} / ¥{{ number_format($item->price) }}</p>
                </div>
            </div>

            {{-- 購入者だけボタン表示 --}}
            @if($isBuyer && !($transaction->buyer_evaluated ?? false))
                <button id="openModal" class="btn btn-success">取引を完了する</button>
            @endif
        </div>

        {{-- 出品者が未評価 && 購入者が評価済みならモーダルを自動表示 --}}
        @php
            $shouldShowEvaluationModal = !$isBuyer && ($transaction->status === 'buyer_completed') && !($transaction->seller_evaluated ?? false);
        @endphp

        {{-- チャットエリア --}}
        <div class="chat-box mb-3" style="max-height: 500px; overflow-y: auto;">
            @foreach($messages as $message)
                <div class="d-flex {{ $message->sender_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }} mb-2">
                    <div class="message p-2 rounded {{ $message->sender_id === auth()->id() ? 'bg-primary text-white' : 'bg-light' }}">
                        {!! nl2br(e($message->message)) !!}
                        @if($message->image_path)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $message->image_path) }}" alt="画像" class="img-thumbnail" width="150">
                            </div>
                        @endif
                        @if($message->sender_id === auth()->id())
                            <div class="text-end small mt-1">
                                <a href="#" class="text-light me-2 small edit-message-btn" data-id="{{ $message->id }}" data-message="{{ e($message->message) }}">編集</a>
                                <form action="{{ route('chat.message.destroy', ['chatMessage' => $message->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('本当に削除しますか？');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link p-0 text-light small">削除</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- メッセージ入力 --}}
        <form action="{{ route('chat.message.store', ['chatRoom' => $chatRoom->id]) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center">
            @csrf
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <input type="text" name="message" class="form-control me-2" placeholder="取引メッセージを記入してください">
            <input type="file" name="image" class="form-control-file me-2" style="max-width: 150px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane">✈</i>
            </button>
        </form>
    </div>
</div>

<div id="editMessageModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h3>メッセージ編集</h3>
        <form id="editMessageForm" method="POST">
            @csrf
            @method('PATCH')
            <textarea name="message" id="editMessageContent" rows="4" required></textarea>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">更新</button>
                <button type="button" id="closeEditModal" class="btn btn-secondary">キャンセル</button>
            </div>
        </form>
    </div>
</div>

<div id="evaluationModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h2>取引完了の評価</h2>
        <form method="POST" action="{{ route('evaluation.store', ['chatRoom' => $chatRoom->id]) }}">
            @csrf
            <p>今回の取引相手はどうでしたか？</p>

            <!-- 星評価 -->
            <div class="stars">
                @for($i = 5; $i >= 1; $i--)
                    <label>
                        <input type="radio" name="score" value="{{ $i }}">
                        <span class="star">&#9733;</span>
                    </label>
                @endfor
            </div>

            <!-- ボタン -->
            <div class="modal-buttons">
                <button type="submit" class="btn btn-primary">送信する</button>
                <button type="button" id="closeModal" class="btn btn-secondary">キャンセル</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // ===== 評価モーダル =====
    const openBtn = document.getElementById('openModal');
    const closeBtn = document.getElementById('closeModal');
    const evaluationModal = document.getElementById('evaluationModal');

    if (openBtn) {
        openBtn.addEventListener('click', () => {
            evaluationModal.style.display = 'flex';
        });
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            evaluationModal.style.display = 'none';
        });
    }

    // ✅ サーバー側で判定したフラグを Blade から受け取る
    const shouldShowModal = @json($shouldShowEvaluationModal);
    if (shouldShowModal && evaluationModal) {
        evaluationModal.style.display = 'flex';
    }

    // ===== メッセージ編集モーダル =====
    document.querySelectorAll('.edit-message-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const messageId = btn.dataset.id;
            const messageText = btn.dataset.message;

            // フォームに値セット
            document.getElementById('editMessageContent').value = messageText;

            // フォームの送信先を動的にセット
            const updateUrl = `{{ route('chat.message.update', ['chatMessage' => ':id']) }}`.replace(':id', messageId);
            document.getElementById('editMessageForm').action = updateUrl;

            // モーダル表示
            document.getElementById('editMessageModal').style.display = 'flex';
        });
    });

    document.getElementById('closeEditModal')?.addEventListener('click', () => {
        document.getElementById('editMessageModal').style.display = 'none';
    });

    // ===== 入力内容の保持（localStorage） =====
    const inputField = document.querySelector('input[name="message"]');
    if (inputField) {
        const chatRoomId = {{ $chatRoom->id }};
        const storageKey = `chat_draft_${chatRoomId}`;
        const savedDraft = localStorage.getItem(storageKey);
        if (savedDraft) inputField.value = savedDraft;

        inputField.addEventListener('input', () => {
            localStorage.setItem(storageKey, inputField.value);
        });

        const form = inputField.closest('form');
        form.addEventListener('submit', () => {
            localStorage.removeItem(storageKey);
        });
    }
});
</script>
@endpush
