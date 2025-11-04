@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $recipe->title }}</h4>
                    <a href="{{ route('admin.parser.recipes') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Назад к списку
                    </a>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if($recipe->image_path)
                                <img src="{{ asset('storage/' . $recipe->image_path) }}" 
                                     alt="{{ $recipe->title }}" 
                                     class="img-fluid rounded mb-3">
                            @else
                                <div class="alert alert-secondary">Изображение отсутствует</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h5>Информация о рецепте</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Просмотры:</th>
                                    <td>{{ number_format($recipe->views, 0, ',', ' ') }}</td>
                                </tr>
                                <tr>
                                    <th>Лайки:</th>
                                    <td><span class="badge bg-success">{{ $recipe->likes }}</span></td>
                                </tr>
                                <tr>
                                    <th>Дизлайки:</th>
                                    <td><span class="badge bg-danger">{{ $recipe->dislikes }}</span></td>
                                </tr>
                                <tr>
                                    <th>Источник:</th>
                                    <td><a href="{{ $recipe->source_url }}" target="_blank">Открыть на сайте</a></td>
                                </tr>
                                <tr>
                                    <th>Добавлено:</th>
                                    <td>{{ $recipe->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($recipe->description)
                        <div class="mt-4">
                            <h5>Описание</h5>
                            <p>{{ $recipe->description }}</p>
                        </div>
                    @endif

                    @if($recipe->nutrition && count($recipe->nutrition) > 0)
                        <div class="mt-4">
                            <h5>Пищевая ценность (на 100г)</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h3>{{ $recipe->nutrition['calories'] ?? 0 }}</h3>
                                            <small>ккал</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h3>{{ $recipe->nutrition['proteins'] ?? 0 }}г</h3>
                                            <small>Белки ({{ $recipe->nutrition['proteins_percent'] ?? 0 }}%)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h3>{{ $recipe->nutrition['fats'] ?? 0 }}г</h3>
                                            <small>Жиры ({{ $recipe->nutrition['fats_percent'] ?? 0 }}%)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h3>{{ $recipe->nutrition['carbs'] ?? 0 }}г</h3>
                                            <small>Углеводы ({{ $recipe->nutrition['carbs_percent'] ?? 0 }}%)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($recipe->ingredients && count($recipe->ingredients) > 0)
                        <div class="mt-4">
                            <h5>Ингредиенты</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Количество</th>
                                        <th>Единица</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recipe->ingredients as $ingredient)
                                        <tr>
                                            <td>{{ $ingredient['name'] ?? '' }}</td>
                                            <td>{{ $ingredient['quantity'] ?? '' }}</td>
                                            <td>{{ $ingredient['measure'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if($recipe->steps && count($recipe->steps) > 0)
                        <div class="mt-4">
                            <h5>Шаги приготовления</h5>
                            @foreach($recipe->steps as $step)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">Шаг {{ $step['step_number'] ?? '' }}</h6>
                                        <p class="card-text">{{ $step['description'] ?? '' }}</p>
                                        @if(isset($step['image']) && $step['image'])
                                            <img src="{{ $step['image'] }}" 
                                                 alt="Шаг {{ $step['step_number'] }}" 
                                                 class="img-fluid rounded mt-2" 
                                                 style="max-height: 300px;">
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4">
                        <form action="{{ route('admin.parser.destroy', $recipe->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Вы уверены, что хотите удалить этот рецепт?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Удалить рецепт
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
