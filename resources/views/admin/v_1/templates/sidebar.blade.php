@can('menu-post')
    <li class="m-menu__item m-menu__item--submenu {{in_array(Route::current()->getName(), ['post', 'category', 'tag']) ? 'm-menu__item--expanded m-menu__item--open' : ''}}" aria-haspopup="true" m-menu-submenu-toggle="hover">
        <a href="javascript:void(0)" class="m-menu__link m-menu__toggle">
            <i class="m-menu__link-icon flaticon-notes"></i>
                <span class="m-menu__link-text">Posts</span>
            <i class="m-menu__ver-arrow la la-angle-right"></i>
         </a>
        <div class="m-menu__submenu "><span class="m-menu__arrow"></span>
            <ul class="m-menu__subnav">
                <li class="m-menu__item  {{Route::current()->getName() ==  'post' ? 'm-menu__item--active' : ''}}" aria-haspopup="true"><a href="{{action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\PostController@index')}}" class="m-menu__link "><i class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span class="m-menu__link-text">All Post</span></a></li>
            </ul>
            <ul class="m-menu__subnav">
                <li class="m-menu__item  {{Route::current()->getName() ==  'category' ? 'm-menu__item--active' : ''}}" aria-haspopup="true"><a href="{{action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\CategoryController@index')}}" class="m-menu__link "><i class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span class="m-menu__link-text">Categories</span></a></li>
            </ul>
            <ul class="m-menu__subnav">
                <li class="m-menu__item  {{Route::current()->getName() ==  'tag' ? 'm-menu__item--active' : ''}}" aria-haspopup="true"><a href="{{action('\Gdevilbat\SpardaCMS\Modules\Post\Http\Controllers\TagController@index')}}" class="m-menu__link "><i class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span class="m-menu__link-text">Tags</span></a></li>
            </ul>
        </div>
    </li>
@endcan