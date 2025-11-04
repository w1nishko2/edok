@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Все рецепты</h4>
                    <a href="{{ route('admin.parser.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Назад к парсеру
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($recipes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Изображение</th>
                                        <th>Название</th>
                                        <th>Просмотры</th>
                                        <th>Лайки</th>
                                        <th>Дата</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recipes as $recipe)
                                        <tr>
                                            <td>{{ $recipe->id }}</td>
                                            <td>
                                                @if($recipe->image_path)
                                                    <img src="{{ asset('storage/' . $recipe->image_path) }}" 
                                                         alt="{{ $recipe->title }}" 
                                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                                @else
                                                    <div style="width: 60px; height: 60px; background: #e9ecef; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                        <small class="text-muted">Нет фото</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($recipe->title, 60) }}</td>
                                            <td>{{ number_format($recipe->views, 0, ',', ' ') }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ $recipe->likes }}</span>
                                                <span class="badge bg-danger">{{ $recipe->dislikes }}</span>
                                            </td>
                                            <td>{{ $recipe->created_at->format('d.m.Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.parser.show', $recipe->id) }}" 
                                                   class="btn btn-sm btn-info">
                                                    Просмотр
                                                </a>
                                                <form action="{{ route('admin.parser.destroy', $recipe->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Вы уверены?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        Удалить
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $recipes->links() }}
                        </div>
                    @else
                        <p class="text-muted">Рецептов пока нет. Запустите парсер!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
