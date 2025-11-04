@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Парсер рецептов 1000.menu</h4>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h2 class="text-primary">{{ $recipesCount }}</h2>
                                    <p class="mb-0">Всего рецептов в базе</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Запустить парсер</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.parser.start') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pages" class="form-label">Количество страниц</label>
                                        <input type="number" 
                                               class="form-control @error('pages') is-invalid @enderror" 
                                               id="pages" 
                                               name="pages" 
                                               value="{{ old('pages', 1) }}" 
                                               min="1" 
                                               max="10" 
                                               required>
                                        <small class="form-text text-muted">От 1 до 10 страниц</small>
                                        @error('pages')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="limit" class="form-label">Максимум рецептов</label>
                                        <input type="number" 
                                               class="form-control @error('limit') is-invalid @enderror" 
                                               id="limit" 
                                               name="limit" 
                                               value="{{ old('limit', 10) }}" 
                                               min="1" 
                                               max="100" 
                                               required>
                                        <small class="form-text text-muted">От 1 до 100 рецептов</small>
                                        @error('limit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-play-fill"></i> Запустить парсер
                                </button>
                                <a href="{{ route('admin.parser.recipes') }}" class="btn btn-secondary">
                                    <i class="bi bi-list"></i> Посмотреть все рецепты
                                </a>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5>Последние добавленные рецепты</h5>
                        </div>
                        <div class="card-body">
                            @if($latestRecipes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Название</th>
                                                <th>Изображение</th>
                                                <th>Дата добавления</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($latestRecipes as $recipe)
                                                <tr>
                                                    <td>{{ $recipe->id }}</td>
                                                    <td>{{ Str::limit($recipe->title, 50) }}</td>
                                                    <td>
                                                        @if($recipe->image_path)
                                                            <img src="{{ asset('storage/' . $recipe->image_path) }}" 
                                                                 alt="{{ $recipe->title }}" 
                                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                                        @else
                                                            <span class="text-muted">Нет фото</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $recipe->created_at->format('d.m.Y H:i') }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.parser.show', $recipe->id) }}" 
                                                           class="btn btn-sm btn-info">
                                                            Просмотр
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">Рецептов пока нет. Запустите парсер!</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
