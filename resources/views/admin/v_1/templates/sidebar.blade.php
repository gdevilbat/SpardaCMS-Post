@can('menu-post')
    <li class="m-menu__item m-menu__item--submenu {{strstr(Route::current()->getName(), 'cms.post') ? 'm-menu__item--expanded m-menu__item--open' : ''}}" aria-haspopup="true" m-menu-submenu-toggle="hover">
        <a href="javascript:void(0)" class="m-menu__link m-menu__toggle">
            <i class="m-menu__link-icon flaticon-notes"></i>
                <span class="m-menu__link-text">{{ trans_choice('post::messages.post', 2) }}</span>
            <i class="m-menu__ver-arrow la la-angle-right"></i>
         </a>
        <div class="m-menu__submenu "><span class="m-menu__arrow"></span>
            <ul class="m-menu__subnav">
                <li class="m-menu__item  {{strstr(Route::current()->getName(), 'cms.post-data') ? 'm-menu__item--active' : ''}}" aria-haspopup="true"><a href="{{route('cms.post-data.master')}}" class="m-menu__link "><i class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span class="m-menu__link-text">All {{ trans_choice('post::messages.post', 1) }}</span></a></li>
            </ul>
            <ul class="m-menu__subnav">
                <li class="m-menu__item  {{strstr(Route::current()->getName(), 'cms.post-category') ? 'm-menu__item--active' : ''}}" aria-haspopup="true"><a href="{{route('cms.post-category.master')}}" class="m-menu__link "><i class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span class="m-menu__link-text">{{ trans_choice('post::messages.post_category', 2) }}</span></a></li>
            </ul>
            <ul class="m-menu__subnav">
                <li class="m-menu__item  {{strstr(Route::current()->getName(), 'cms.post-tag') ? 'm-menu__item--active' : ''}}" aria-haspopup="true"><a href="{{route('cms.post-tag.master')}}" class="m-menu__link "><i class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span class="m-menu__link-text">{{ trans_choice('post::messages.post_tag', 2) }}</span></a></li>
            </ul>
        </div>
    </li>
@endcan