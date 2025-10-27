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

    {{-- チャットメイン --}}
    <div class="chat-main ">
        @php
            $transaction = $chatRoom->transaction;
            $isBuyer = auth()->id() === $chatRoom->buyer_id;
        @endphp

        <div class="chat-header">
            <div class="chat-header-top">
                <div class="profile-info d-flex align-items-center">
                {{-- 相手ユーザー画像 --}}
                @php
                    $imagePath = $partner->avatar_path;

                    if (empty($imagePath)) {
                        // null または空欄ならデフォルト
                        $imagePath = asset('images/default-avatar.png');
                    } elseif (preg_match('/^https?:\/\//', $imagePath)) {
                        // すでにフルURLならそのまま
                        $imagePath = $imagePath;
                    } elseif (str_starts_with($imagePath, 'storage/')) {
                        // storageパスなら asset() で補完
                        $imagePath = asset($imagePath);
                    } elseif (str_starts_with($imagePath, 'images/')) {
                        // public/images 配下も asset() で補完
                        $imagePath = asset($imagePath);
                    } else {
                        // それ以外（DBがパスのみ）なら storage 配下として扱う
                        $imagePath = asset('storage/' . $imagePath);
                    }
                @endphp
                    <img src="{{ $imagePath }}" alt="{{ $partner->name }}さんのプロフィール画像" class="profile-img">
                        <h5 class="mb-1 ms-2">{{ $partner->name }}さんとの取引画面</h5>
                </div>

                    {{-- 取引を完了するボタン --}}
                    @if($isBuyer)
                        <button id="openModal" class="btn btn-success" 
                            @if($transaction->buyer_evaluated ?? false) disabled @endif>
                            取引を完了する
                        </button>
                    @endif
            </div>

            {{-- 商品情報 --}}
            <div class="item-info">
                <img src="{{ asset('storage/' . optional($item->item_images->first())->image_path) }}" 
                    alt="{{ $item->title }}" class="item-image me-3">
                <div>
                    <p class="item-title">{{ $item->title }}</p>
                    <p class="item-price">¥{{ number_format($item->price) }}</p>
                </div>
            </div>
        </div>

        {{-- 出品者が未評価 && 購入者が評価済みならモーダルを自動表示 --}}
        @php
            $shouldShowEvaluationModal = !$isBuyer && ($transaction->status === 'buyer_completed') && !($transaction->seller_evaluated ?? false);
        @endphp

        {{-- チャットエリア --}}
        <div class="chat-box">
            @foreach($messages as $message)
                @php
                    $isMine = $message->sender_id === auth()->id();
                    $sender = $message->sender;
                    $senderName = $sender->name ?? '不明なユーザー';
                    $senderImage = $sender->avatar_path
                        ? asset('storage/' . $sender->avatar_path)
                        : asset('images/default-avatar.png');
                @endphp

                <div class="chat-message mb-3 {{ $isMine ? 'mine' : 'partner' }}">
                    {{-- 送信者情報（名前＋画像） --}}
                    <div class="message-header {{ $isMine ? 'mine' : 'partner' }}">
                        @if($isMine)
                            <span class="sender-name">{{ $senderName }}</span>
                            <img src="{{ $senderImage }}" alt="プロフィール画像" class="sender-avatar">
                        @else
                            <img src="{{ $senderImage }}" alt="プロフィール画像" class="sender-avatar">
                            <span class="sender-name">{{ $senderName }}</span>
                        @endif
                    </div>

                    {{-- 吹き出し部分 --}}
                    <div class="{{ $isMine ? 'text-end' : 'text-start' }}">
                        <div class="message rounded {{ $isMine ? 'bg-primary text-white' : 'bg-light' }}">
                            {!! nl2br(e($message->message)) !!}
                            @if($message->image_path)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $message->image_path) }}" alt="画像" class="img-thumbnail" width="150">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- メッセージ入力 --}}
        @php
            $isTransactionCompleted = $transaction->buyer_evaluated && $transaction->seller_evaluated;
        @endphp

        <form action="{{ route('chat.message.store', ['chatRoom' => $chatRoom->id]) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center">
            @csrf
            <input type="text" name="message" class="form-control me-2" placeholder="取引メッセージを記入してください"
                @if($isTransactionCompleted) disabled @endif>
            <input type="file" 
                id="imageInput" 
                name="image" 
                class="d-none" 
                accept="image/*"
                @if($isTransactionCompleted) disabled @endif>

            {{-- 「画像を追加」ボタン --}}
            <button type="button" 
                    class="btn btn-outline-secondary me-2" 
                    id="addImageButton"
                    @if($isTransactionCompleted) disabled @endif>
                    画像を追加
            </button>

            <button type="submit" 
                    class="btn btn-light"
                    @if($isTransactionCompleted) disabled @endif>
                <i class="fa-regular fa-paper-plane"></i>
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
        <h2>取引が完了しました。</h2>
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
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('addImageButton').addEventListener('click', function() {
    document.getElementById('imageInput').click();
});

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
