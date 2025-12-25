<div class="form-msg">
    @if (session('success'))
        <div class="notification-content">
            <div class="notification success"> {{ session('success') }}</div>
        </div>
    @elseif (session('error'))
        <div class="notification-content">
            <div class="notification error"> {{ session('error') }}</div>
        </div>
    @elseif (session('info'))
        <div class="notification-content">
            <div class="notification info"> {{ session('info') }}</div>
        </div>
    @endif
</div>
