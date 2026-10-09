<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use League\CommonMark\CommonMarkConverter;

class Article extends Model
{
    /**
     * De enige categorie die niet uit eigen redactie komt: deze artikelen worden door
     * NewsCrawlService automatisch herschreven uit externe motornieuwsbronnen. Eigen
     * kennisartikelen citeren vaak wél een bron (Rijksoverheid, CBR), dus source_url is
     * geen bruikbaar onderscheid.
     */
    public const NEWS_CATEGORY = 'Nieuwe releases';

    protected $attributes = [
        'author_name' => 'Jake en Rory Andreas',
        'author_bio' => 'Broers en oprichters van RevRace. Ze rijden trackdays op een Honda CB1300 en een KTM 1290 Super Duke. Kennisartikelen onderbouwen ze met officiële bronnen, "nieuwe releases" worden automatisch herschreven uit geverifieerde motornieuwsbronnen.',
    ];

    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'body',
        'cover_image_url',
        'source_name',
        'source_url',
        'author_name',
        'author_bio',
        'meta_description',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function renderedBody(): string
    {
        static $converter;
        $converter ??= new CommonMarkConverter(['html_input' => 'strip']);

        return (string) $converter->convert($this->body);
    }
}
