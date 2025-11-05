@foreach($recipes as $index => $recipe)
    <div class="col-6 col-sm-6 col-lg-4 col-xl-3 recipe-card-item">
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
                 
                    
                    @php
                        $hasCalories = $recipe->nutrition && isset($recipe->nutrition['calories']) && $recipe->nutrition['calories'] > 0;
                        $hasCaloriesFromMacros = $recipe->nutrition && (
                            (isset($recipe->nutrition['proteins']) && $recipe->nutrition['proteins'] > 0) ||
                            (isset($recipe->nutrition['fats']) && $recipe->nutrition['fats'] > 0) ||
                            (isset($recipe->nutrition['carbs']) && $recipe->nutrition['carbs'] > 0)
                        );
                        $showNutrition = $hasCalories || $hasCaloriesFromMacros;
                    @endphp
                    
                   
                </div>
            </article>
        </a>
    </div>
@endforeach
