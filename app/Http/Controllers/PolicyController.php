<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PolicyController extends Controller
{
    /**
     * Показать страницу политики конфиденциальности
     */
    public function privacy()
    {
        return view('policy.privacy-policy', [
            'title' => 'Политика конфиденциальности',
            'description' => 'Политика конфиденциальности сайта ЯЕдок - информация о сборе и использовании персональных данных пользователей'
        ]);
    }

    /**
     * Показать страницу условий использования
     */
    public function terms()
    {
        return view('policy.terms', [
            'title' => 'Пользовательское соглашение',
            'description' => 'Условия использования сайта ЯЕдок - правила и ответственность пользователей'
        ]);
    }

    /**
     * Показать страницу контактов
     */
    public function contact()
    {
        return view('policy.contact', [
            'title' => 'Контакты',
            'description' => 'Связаться с нами - контактная информация сайта ЯЕдок, наши каналы и документы'
        ]);
    }
}
