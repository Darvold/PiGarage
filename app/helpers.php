<?php
if (!function_exists('formatPhoneNumber')) {
    function formatPhoneNumber($phoneNumber)
    {
        // Убедитесь, что номер телефона содержит только цифры
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Пример маски для российского номера телефона: +7 (999) 999-99-99
        if (strlen($phoneNumber) == 11) {
            return '+7(' . substr($phoneNumber, 1, 3) . ')' . substr($phoneNumber, 4, 3) . '-' . substr($phoneNumber, 7, 2) . '-' . substr($phoneNumber, 9, 2);
        }

        // Если номер телефона не соответствует маске, возвращаем как есть
        return $phoneNumber;
    }

    function formatHomePhoneNumber($phoneNumber)
    {
        // Убедитесь, что номер телефона содержит только цифры
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (strlen($phoneNumber) == 5) {
            return substr($phoneNumber, 0, 2) . '-' . substr($phoneNumber, 2, 3);
        } elseif (strlen($phoneNumber) == 6) {
            return substr($phoneNumber, 0, 3) . '-' . substr($phoneNumber, 3, 3);
        } elseif (strlen($phoneNumber) == 7) {
            return substr($phoneNumber, 0, 1) . '-' . substr($phoneNumber, 1, 3) . '-' . substr($phoneNumber, 4, 3);
        }

        // Если номер телефона не соответствует маске, возвращаем как есть
        return $phoneNumber;
    }
}
