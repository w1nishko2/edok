@foreach($recipes as $index => $recipe)
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3 recipe-card-item">
        <a href="{{ route('recipe.show', $recipe->slug) }}" class="card-link" itemprop="url">
            <article class="custom-card" itemscope itemtype="https://schema.org/Recipe">
                <meta itemprop="position" content="{{ $index + 1 }}">
                <meta itemprop="url" content="{{ route('recipe.show', $recipe->slug) }}">
                <meta itemprop="datePublished" content="{{ $recipe->created_at->toIso8601String() }}">
                
                @if($recipe->image_path)
                    <img src="{{ Storage::url($recipe->image_path) }}" 
                         class="custom-card-img" 
                         alt="{{ $recipe->title }}"
                         itemprop="image"
                         loading="lazy"
                         width="400"
                         height="300">
                @else
                    <div class="custom-card-img-placeholder">
                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                    </div>
                @endif
                
                <div class="custom-card-body">
                    <h5 class="custom-card-title" itemprop="name">{{ Str::limit($recipe->title, 60) }}</h5>
                 
                    
                    <div class="custom-card-stats">
                        <div class="custom-stats-left">
                            @if($recipe->views)
                                <span class="custom-stat">
                                    <i class="bi bi-eye"></i> {{ number_format($recipe->views, 0, ',', ' ') }}
                                </span>
                            @endif
                            @if($recipe->likes)
                                <span class="custom-stat">
                                    <i class="bi bi-hand-thumbs-up"></i> {{ $recipe->likes }}
                                </span>
                            @endif
                            @if($recipe->rating > 0)
                                <span class="custom-stat" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
                                    <i class="bi bi-star-fill text-warning"></i> 
                                    <span itemprop="ratingValue">{{ number_format($recipe->rating, 1) }}</span>
                                    <meta itemprop="ratingCount" content="{{ $recipe->rating_count }}">
                                    <meta itemprop="bestRating" content="5">
                                </span>
                            @endif
                        </div>
                        @if($recipe->nutrition && isset($recipe->nutrition['calories']))
                            <span class="custom-stat" itemprop="nutrition" itemscope itemtype="https://schema.org/NutritionInformation">
                                <i class="bi bi-fire"></i> 
                                <span itemprop="calories">{{ $recipe->nutrition['calories'] }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </article>
        </a>
    </div>
@endforeach
