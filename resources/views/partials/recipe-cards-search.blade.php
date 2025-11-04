@foreach($recipes as $recipe)
    <div class="col-md-4 recipe-card-item">
        <a href="{{ route('recipe.show', $recipe->slug) }}" class="card-link">
            <div class="custom-card h-100">
                @if($recipe->image_path)
                    <img src="{{ asset('storage/' . $recipe->image_path) }}" 
                         class="custom-card-img" 
                         alt="{{ $recipe->title }}"
                         loading="lazy">
                @else
                    <div class="custom-card-img-placeholder">
                        <i class="bi bi-image"></i>
                    </div>
                @endif
                
                <div class="custom-card-body">
                    <h5 class="custom-card-title">{{ $recipe->title }}</h5>
                    
                    @if($recipe->description)
                        <p class="custom-card-text">
                            {{ Str::limit(strip_tags($recipe->description), 100) }}
                        </p>
                    @endif
                    
                    <div class="custom-card-stats">
                        <div class="custom-stats-left">
                            @if($recipe->total_time)
                                <span class="custom-stat">
                                    <i class="bi bi-clock"></i> {{ $recipe->total_time }}
                                </span>
                            @endif
                            @if($recipe->difficulty)
                                <span class="custom-stat">
                                    <i class="bi bi-bar-chart"></i> {{ $recipe->difficulty }}
                                </span>
                            @endif
                        </div>
                        
                        @if(isset($recipe->similarity_score))
                            <div class="similarity-badge">
                                <i class="bi bi-percent"></i> {{ $recipe->similarity_score }}%
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </a>
    </div>
@endforeach
