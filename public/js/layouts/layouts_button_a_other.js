document.querySelector('.btn-save-modern')?.addEventListener('click', function() {
    this.classList.add('saving');
    setTimeout(() => {
        this.classList.remove('saving');
        this.innerHTML = '<i class="fas fa-check"></i> Сохранено!';
        this.style.background = 'var(--success)';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-save"></i> Сохранить изменения';
            this.style.background = '';
        }, 2000);
    }, 1500);
});