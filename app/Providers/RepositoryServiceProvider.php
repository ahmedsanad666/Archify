<?php

namespace App\Providers;

use App\Repositories\Contracts\AboutPageRepositoryInterface;
use App\Repositories\Contracts\BlogCategoryRepositoryInterface;
use App\Repositories\Contracts\BlogRepositoryInterface;
use App\Repositories\Contracts\ConceptRepositoryInterface;
use App\Repositories\Contracts\CoreValueRepositoryInterface;
use App\Repositories\Contracts\FaqRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\LeadRepositoryInterface;
use App\Repositories\Contracts\ProjectCategoryRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use App\Repositories\Contracts\SliderRepositoryInterface;
use App\Repositories\Contracts\StatisticRepositoryInterface;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use App\Repositories\Contracts\TestimonialRepositoryInterface;
use App\Repositories\Eloquent\AboutPageRepository;
use App\Repositories\Eloquent\BlogCategoryRepository;
use App\Repositories\Eloquent\BlogRepository;
use App\Repositories\Eloquent\ConceptRepository;
use App\Repositories\Eloquent\CoreValueRepository;
use App\Repositories\Eloquent\FaqRepository;
use App\Repositories\Eloquent\LanguageRepository;
use App\Repositories\Eloquent\LeadRepository;
use App\Repositories\Eloquent\ProjectCategoryRepository;
use App\Repositories\Eloquent\ProjectRepository;
use App\Repositories\Eloquent\ServiceRepository;
use App\Repositories\Eloquent\SiteSettingRepository;
use App\Repositories\Eloquent\SliderRepository;
use App\Repositories\Eloquent\StatisticRepository;
use App\Repositories\Eloquent\TeamMemberRepository;
use App\Repositories\Eloquent\TestimonialRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        LanguageRepositoryInterface::class => LanguageRepository::class,
        SiteSettingRepositoryInterface::class => SiteSettingRepository::class,
        FaqRepositoryInterface::class => FaqRepository::class,
        SliderRepositoryInterface::class => SliderRepository::class,
        ServiceRepositoryInterface::class => ServiceRepository::class,
        AboutPageRepositoryInterface::class => AboutPageRepository::class,
        StatisticRepositoryInterface::class => StatisticRepository::class,
        CoreValueRepositoryInterface::class => CoreValueRepository::class,
        ProjectCategoryRepositoryInterface::class => ProjectCategoryRepository::class,
        ProjectRepositoryInterface::class => ProjectRepository::class,
        ConceptRepositoryInterface::class => ConceptRepository::class,
        BlogCategoryRepositoryInterface::class => BlogCategoryRepository::class,
        BlogRepositoryInterface::class => BlogRepository::class,
        TeamMemberRepositoryInterface::class => TeamMemberRepository::class,
        TestimonialRepositoryInterface::class => TestimonialRepository::class,
        LeadRepositoryInterface::class => LeadRepository::class,
    ];
}
