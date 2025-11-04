@foreach($recipes as $recipe)
    <a href="{{ route('recipe.show', $recipe->slug) }}" class="recipe-link recipe-card-item">
        <article class="recipe-card">
            @if($recipe->image_path)
                <img src="{{ asset('storage/' . $recipe->image_path) }}" 
                     alt="{{ $recipe->title }}" 
                     class="recipe-image"
                     loading="lazy">
            @else
                <div class="recipe-image-placeholder">
                    <i class="bi bi-image"></i>
                </div>
            @endif
            
            <div class="recipe-content">
                <h3 class="recipe-title">{{ $recipe->title }}</h3>
                
                @if($recipe->description)
                    <p class="recipe-description">{{ Str::limit($recipe->description, 100) }}</p>
                @endif
                
                <div class="recipe-meta">
                    @if($recipe->total_time)
                        <span class="meta-item">
                            <i class="bi bi-clock"></i>
                            {{ $recipe->total_time }} мин
                        </span>
                    @endif
                    
                    @if($recipe->views)
                        <span class="meta-item">
                            <i class="bi bi-eye"></i>
                            {{ $recipe->views }}
                        </span>
                    @endif
                </div>
            </div>
        </article>
    </a>
@endforeach
