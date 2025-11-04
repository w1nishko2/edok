@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Админ-панель</h4>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Добро пожаловать в админ-панель!
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Функционал парсера</h5>
                            <p class="text-muted">Здесь будет размещен парсер и другой функционал для администраторов.</p>
                            
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6 class="card-title">Статистика</h6>
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="p-3 border rounded">
                                                <h3 class="text-primary">{{ \App\Models\User::count() }}</h3>
                                                <p class="mb-0 text-muted">Пользователей</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 border rounded">
                                                <h3 class="text-success">{{ \App\Models\User::where('role', 'admin')->count() }}</h3>
                                                <p class="mb-0 text-muted">Администраторов</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 border rounded">
                                                <h3 class="text-info">0</h3>
                                                <p class="mb-0 text-muted">Запущено парсеров</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6 class="card-title">Управление парсером</h6>
                                    <p class="text-muted">Парсер рецептов с сайта 1000.menu</p>
                                    <a href="{{ route('admin.parser.index') }}" class="btn btn-primary">
                                        <i class="bi bi-play-fill"></i> Перейти к парсеру
                                    </a>
                                    <a href="{{ route('admin.parser.recipes') }}" class="btn btn-secondary">
                                        <i class="bi bi-list"></i> Посмотреть рецепты ({{ \App\Models\Recipe::count() }})
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
