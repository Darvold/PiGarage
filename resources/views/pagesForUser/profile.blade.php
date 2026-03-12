@extends('layouts.mainUser', ['ProfileUserStyles' => ['profile.css']])
@section('pages')
<div class="profile-component">
	<!-- Хедер профиля -->
	<div class="profile-header">
		<div class="profile-title-section">
			<h1 class="profile-title">
				<i class="fas fa-user-cog"></i>
				Панель управления профилем
			</h1>
			<p class="profile-subtitle">Управление персональными данными</p>
		</div>

		<div class="profile-actions">
		</div>
	</div>

	<!-- Основная часть профиля -->
	<div class="profile-content">
		<!-- Левая колонка - навигация и фото -->
		<div class="profile-sidebar">
			<div class="photo-card">
				<div class="photo-container">
					<div class="profile-photo">
						<div class="photo-placeholder">
							<i class="fas fa-user"></i>
							<span></span>
						</div>
					</div>
                    {{-- <button class="change-photo-btn">
                        <i class="fas fa-camera"></i>
                        Изменить фото
                    </button> --}}
                </div>
                <div class="user-quick-info">
                	<h4>{{$userCurrent->short_fio}}</h4>
                	<p><i class="fas fa-phone"></i>{{$userCurrent->formatted_phone}}</p>
                </div>
            </div>
        </div>

        <!-- Правая колонка - форма редактирования -->
        <div class="profile-form-section">
        	<div class="form-header">
        		<h2>
        			<i class="fas fa-id-card"></i>
        			Профиль пользователя
        		</h2>
        		{{-- <div class="form-status">
        			<span class="status-badge verified">
        				<i class="fas fa-check-circle"></i>
        				Верифицирован
        			</span>
        		</div> --}}
        	</div>

        	<div class="form-card">
        		<div class="form-card-header">
        			<h3>
        				<i class="fas fa-user-edit"></i>
        				Управление персональными данными
        			</h3>
        		</div>

        		<form id="profileForm" class="profile-form" method="post" action="{{route('updateProfileUser.store')}}">
        			@csrf
        			<!-- ФИО -->
        			<div class="form-group">
        				<label class="form-label">
        					<i class="fas fa-signature"></i>
        					ФИО
        					<span class="label-hint">Полное имя как в паспорте</span>
        				</label>
        				<div class="input-group">
        					<input
        					type="text"
        					name="fio"
        					class="form-control"
        					value="{{ $userCurrent->fio }}"
        					placeholder="Введите ФИО"
        					required
        					>
        					<div class="input-hint">
        						<i class="fas fa-info-circle"></i>
        						Используется для документов
        					</div>
        				</div>
        			</div>

        			<!-- Email -->
        			<div class="form-group">
        				<label class="form-label">
        					<i class="fas fa-envelope"></i>
        					Email
                            {{-- <span class="label-status verified">
                                <i class="fas fa-check"></i>
                                Подтвержден
                            </span> --}}
                        </label>
                        <div class="input-group">
                        	<input
                        	type="email"
                        	class="form-control"
                        	name="email"
                        	value="{{$userCurrent->email ?? ""}}"
                        	placeholder="Введите email"
                        	>
                        	<button type="button" class="btn-icon verify-btn">
                        		<i class="fas fa-sync-alt"></i>
                        		Подтвердить
                        	</button>
                        </div>
                    </div>

                    <!-- Телефон -->
                    <div class="form-group">
                    	<label class="form-label">
                    		<i class="fas fa-phone"></i>
                    		Телефон
                    		<span class="label-hint">Использоваться для входа</span>
                    	</label>
                    	<div class="input-group">
                    		<input
                    		type="tel"
                    		data-mask="phone"
                    		name="phone"
                    		class="form-control"
                    		value="{{$userCurrent->formatted_phone}}"
                    		placeholder="Введите номер телефона"
                    		required
                    		>
                    	</div>
                    </div>

                    <!-- Дата регистрации -->
                    <div class="form-group">
                    	<label class="form-label">
                    		<i class="fas fa-calendar-alt"></i>
                    		Дата регистрации
                    	</label>
                    	<div class="input-group">
                    		<input
                    		type="text"
                    		class="form-control"
                    		value="{{$userCurrent->created_at->translatedFormat('d F Y')}}"
                    		readonly
                    		disabled
                    		>
                    		<div class="input-hint">
                    			<i class="fas fa-clock"></i>
                    			Автоматически заполняется системой
                    		</div>
                    	</div>
                    </div>

                    <!-- Регион -->
                    <div class="form-group">
                    	<label class="form-label">
                    		<i class="fas fa-map-marker-alt"></i>
                    		Регион
                    	</label>
                    	<div class="form-group">
                    <div class="input-with-icon">
                        <select class="js-select-region" name="region">
                            <option>{{$userCurrent->region}}</option>
                        </select>
                    </div>
                </div>
                    </div>

                    <!-- Дополнительные поля -->
{{--                     <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">
                                <i class="fas fa-birthday-cake"></i>
                                Дата рождения
                            </label>
                            <input
                                type="date"
                                class="form-control"
                                value="1990-01-15"
                            >
                        </div>

                        <div class="form-group half">
                            <label class="form-label">
                                <i class="fas fa-venus-mars"></i>
                                Пол
                            </label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="gender" checked>
                                    <span class="radio-custom"></span>
                                    Мужской
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="gender">
                                    <span class="radio-custom"></span>
                                    Женский
                                </label>
                            </div>
                        </div>
                    </div> --}}

                    <!-- Кнопки действий -->
                    <div class="form-actions">
                    	<button type="submit" id="saveProfileBtn" class="btn-save-modern">
                    		<span class="btn-content">
                    			<i class="fas fa-save"></i>
                    			<span class="btn-text">Сохранить изменения</span>
                    		</span>
                    		<span class="btn-check">
                    			<i class="fas fa-check"></i>
                    		</span>
                    	</button>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
	// Инициализация компонента профиля
	document.addEventListener('DOMContentLoaded', function() {
		const profileForm = document.getElementById('profileForm');
		const saveBtn = document.getElementById('saveProfileBtn');

    // Сохранение формы
		if (profileForm) {
			profileForm.addEventListener('submit', function(e) {
				e.preventDefault();

            // Показываем индикатор загрузки
				saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';
				saveBtn.disabled = true;
				profileForm.submit();
            // Имитация отправки данных
				setTimeout(() => {
					saveBtn.innerHTML = '<i class="fas fa-check"></i> Сохранено!';
					saveBtn.style.background = 'var(--success)';

                // Показываем уведомление
					showNotification('Данные успешно сохранены', 'success');

                // Возвращаем кнопку в исходное состояние
					setTimeout(() => {
						saveBtn.innerHTML = '<i class="fas fa-save"></i> Сохранить изменения';
						saveBtn.style.background = '';
						saveBtn.disabled = false;
					}, 2000);
				}, 1500);
			});
		}

    // Кнопка подтверждения email
		document.querySelectorAll('.verify-btn').forEach(btn => {
			btn.addEventListener('click', function() {
				this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
				this.style.background = 'var(--warning)';

				setTimeout(() => {
					this.innerHTML = '<i class="fas fa-check"></i> Отправлено';
					this.style.background = 'var(--success)';

					showNotification('Ссылка для подтверждения отправлена на email', 'success');

					setTimeout(() => {
						this.innerHTML = '<i class="fas fa-sync-alt"></i> Подтвердить';
						this.style.background = 'var(--primary)';
					}, 3000);
				}, 2000);
			});
		});

    // Кнопка изменения фото
		document.querySelector('.change-photo-btn')?.addEventListener('click', function() {
			const input = document.createElement('input');
			input.type = 'file';
			input.accept = 'image/*';

			input.onchange = function(e) {
				const file = e.target.files[0];
				if (file) {
					const reader = new FileReader();
					reader.onload = function(event) {
						const img = document.createElement('img');
						img.src = event.target.result;
						img.style.width = '100%';
						img.style.height = '100%';
						img.style.borderRadius = '50%';
						img.style.objectFit = 'cover';

						const placeholder = document.querySelector('.photo-placeholder');
						placeholder.innerHTML = '';
						placeholder.appendChild(img);

						showNotification('Фото успешно обновлено', 'success');
					};
					reader.readAsDataURL(file);
				}
			};

			input.click();
		});

    // Навигация
		document.querySelectorAll('.profile-nav .nav-item').forEach(item => {
			item.addEventListener('click', function(e) {
				e.preventDefault();

            // Убираем активный класс у всех
				document.querySelectorAll('.profile-nav .nav-item').forEach(i => {
					i.classList.remove('active');
				});

            // Добавляем активный класс текущему
				this.classList.add('active');

            // Имитация загрузки контента
				const section = this.textContent.trim();
				showNotification(`Загружаем раздел: ${section}`, 'info');
			});
		});

    // Функция показа уведомлений
		function showNotification(message, type = 'info') {
			const notification = document.createElement('div');
			notification.className = `notification notification-${type}`;
			notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            ${message}
			`;

			notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? 'var(--success)' : 'var(--primary)'};
            color: white;
            padding: 16px 24px;
            border-radius: var(--radius-sm);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
			`;

			document.body.appendChild(notification);

			setTimeout(() => {
				notification.style.animation = 'slideOut 0.3s ease';
				setTimeout(() => notification.remove(), 300);
			}, 3000);

        // Добавляем CSS анимации
			const style = document.createElement('style');
			style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
			`;
			document.head.appendChild(style);
		}

	});

</script>
@endsection
