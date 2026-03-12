document.addEventListener('DOMContentLoaded', function() {
    // Элементы DOM
    const loginForm = document.getElementById('loginForm');
    const togglePassword = document.querySelectorAll('#togglePassword');
    const passwordInput = document.getElementById('password');
    const errorAlert = document.getElementById('errorAlert');
    const successAlert = document.getElementById('successAlert');
    // Показать/скрыть пароль для каждого элемента
    togglePassword.forEach(function(button) {
        button.addEventListener('click', function() {
        // Находим соответствующий input рядом с кнопкой
            const passwordInput = this.closest('.input-with-icon').querySelector('input[type="password"], input[type="text"]');
            
        // Меняем тип input
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
        // Меняем иконку
            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });


    // Добавляем валидацию в реальном времени
    const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('invalid');
        });

        input.addEventListener('blur', function() {
            if (this.required && !this.value.trim()) {
                this.classList.add('invalid');
            }
        });
    });

    // Стили для невалидных полей
    const style = document.createElement('style');
    style.textContent = `
        .invalid {
            border-color: var(--danger) !important;
        }

        .input-with-icon .invalid {
            border-color: var(--danger) !important;
        }
    `;
    document.head.appendChild(style);


});

    /*маска номера телефона*/
document.addEventListener('DOMContentLoaded', () => {
    const inputElement = document.querySelector('[data-mask="phone"]')
    const maskOptions = { // создаем объект параметров
        mask: '+{7}(000)000-00-00' // задаем единственный параметр mask
    }
    IMask(inputElement, maskOptions) // запускаем плагин с переданными параметрами
})

$('.js-select-region').select2({
    placeholder: "Выберите регион",
    language: "ru"
});

if (typeof cities !== 'undefined' && Array.isArray(cities)) {
    populateRegions(cities);
} else {
    console.error('Данные о городах не загружены или данные не являются массивом.');
}

    // Функция для заполнения <select> регионами
function populateRegions(data) {
    const selectRegion = $('.js-select-region');
    const uniqueSubjects = new Set();

    data.forEach(item => {
        uniqueSubjects.add(item.subject);
    });

    uniqueSubjects.forEach(subject => {
        const option = $('<option></option>').val(subject).text(subject);
        selectRegion.append(option);
    });
}

    // Обработчик события 'change' для выбора региона
$('.js-select-region').on('change', function () {
    selectedRegion = $(this).val();
});